<?php
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *
 * $Id: Tx_Formhandler_Interceptor_Default.php 19238 2009-04-20 15:03:36Z reinhardfuehricht $
 *                                                                        */

/**
 * Spam protection for the form withouth Captcha. 
 * It parses the time the user needs to fill out the form. 
 * If the time is below a minimum time or over a maximum time, the submission is treated as Spam.
 * If Spam is detected you can redirect the user to a custom page 
 * or use the Subpart ###TEMPLATE_ANTISPAM### to just display something.
 * 
 * Example:
 * <code>
 * saveInterceptors.1.class = Tx_Formhandler_Interceptor_AntiSpamFormTime
 *
 * saveInterceptors.1.config.redirectPage = 17
 * saveInterceptors.1.config.minTime.value = 5
 * saveInterceptors.1.config.minTime.unit = seconds
 * saveInterceptors.1.config.maxTime.value = 5
 * saveInterceptors.1.config.maxTime.unit = minutes
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Interceptor
 */
class Tx_Formhandler_Interceptor_AntiSpamFormTime extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @param array $gp The GET/POST parameters
	 * @param array $settings The defined TypoScript settings for the finisher
	 * @return array The probably modified GET/POST parameters
	 */
	public function process($gp, $settings) {
		$this->gp = $gp;
		$this->settings = $settings;
		$isSpam = $this->doCheck();
		if($isSpam) {
			$this->log();
			if($this->settings['redirectPage']) {
				$this->doRedirect($this->settings['redirectPage']);
			} else {
				$view = $this->componentManager->getComponent('Tx_Formhandler_View_AntiSpam');
				$view->setLangFile($this->langFile);
				$view->setPredefined($this->predefined);
				
				$templateCode = $this->getTemplate();
				$view->setTemplate($templateCode, 'ANTISPAM');
				if(!$view->hasTemplate()) {
					Tx_Formhandler_StaticFuncs::throwException('SPAM!!!!!!');
					return 'Lousy spammer!';
				}
				
				return $view->render($this->gp, array());
				
				
				
			}
		}
		
		return $this->gp;
	}
	
	/**
	 * Loads the template file.
	 *
	 * @return string The template code
	 */
	protected function getTemplate() {
		$templateFile = $this->settings['templateFile'];
		if(isset($this->settings['templateFile.']) && is_array($this->settings['templateFile.'])) {
			$templateFile = $this->cObj->cObjGetSingle($this->settings['templateFile'], $this->settings['templateFile.']);
		} else {
			$templateFile = Tx_Formhandler_StaticFuncs::resolvePath($templateFile);
		}
		$template = t3lib_div::getURL($templateFile);
		
		return $template;
	}
	
	/**
	 * Performs checks if the submitted form should be treated as Spam.
	 *
	 * @return boolean
	 */
	protected function doCheck() {
		
		$value = $this->settings['minTime.']['value'];
		$unit = $this->settings['minTime.']['unit'];
		$minTime = Tx_Formhandler_StaticFuncs::convertToSeconds($value, $unit);
		
		
		$value = $this->settings['maxTime.']['value'];
		$unit = $this->settings['maxTime.']['unit'];
		$maxTime = Tx_Formhandler_StaticFuncs::convertToSeconds($value, $unit);
		$spam = FALSE;
		if (	!isset($this->gp['formtime']) || 
				!is_numeric($this->gp['formtime'])) {
					
			$spam = TRUE;
		} elseif($minTime && time() - intval($this->gp['formtime']) < $minTime) {
			$spam = TRUE;
		} elseif($maxTime && time() - intval($this->gp['formtime']) > $maxTime) {
			$spam = TRUE;
		}
		return $spam;
	}
	
	/**
	 * Logs to the database. Records will be marked red in backend module.
	 *
	 * @return void
	 */
	protected function log() {
		$logger = $this->componentManager->getComponent('Tx_Formhandler_Logger_DB');
		$logger->log($this->gp, array('markAsSpam' => 1));
	}
	
	/**
	 * Redirects to a specified page or URL.
	 *
	 * @return void
	 */
	protected function doRedirect($redirect) {
		$url = '';

		if(!isset($redirect)) {
			return;
		}

		//if redirect_page was page id
		if (is_numeric($redirect)) {

			// these parameters have to be added to the redirect url
			$addparams = array();
			if (t3lib_div::_GP('L')) {
				$addparams['L'] = t3lib_div::_GP('L');
			}
				
			$url = $this->cObj->getTypoLink_URL($redirect, '', $addparams);
				
			//else it may be a full URL
		} else {
			$url = $redirect;
		}

		
		if($url) {
			header('Location: ' . t3lib_div::locationHeaderUrl($url));
		}
		exit();
	}

}
?>
