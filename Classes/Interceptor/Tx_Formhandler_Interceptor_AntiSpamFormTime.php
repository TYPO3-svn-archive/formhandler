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
 * $Id$
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
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$isSpam = $this->doCheck();
		if ($isSpam) {
			$this->log(TRUE);
			if ($this->settings['redirectPage']) {
				Tx_Formhandler_Globals::$session->reset();
				Tx_Formhandler_Staticfuncs::doRedirect($this->settings['redirectPage'], $this->settings['correctRedirectUrl']);
				return 'Lousy spammer!';
			} else {
				$view = $this->componentManager->getComponent('Tx_Formhandler_View_AntiSpam');
				$view->setLangFiles(Tx_Formhandler_Globals::$langFiles);
				$view->setPredefined($this->predefined);
				
				$templateCode = Tx_Formhandler_Globals::$templateCode;
				$view->setTemplate($templateCode, 'ANTISPAM');
				if (!$view->hasTemplate()) {
					Tx_Formhandler_StaticFuncs::throwException('spam_detected');
					return 'Lousy spammer!';
				}
				$content = $view->render($this->gp, array());
				Tx_Formhandler_Globals::$session->reset();
				return $content;
			}
		}
		return $this->gp;
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
		if (!isset($this->gp['formtime']) || 
			!is_numeric($this->gp['formtime'])) {

			$spam = TRUE;
		} elseif ($minTime && time() - intval($this->gp['formtime']) < $minTime) {
			$spam = TRUE;
		} elseif ($maxTime && time() - intval($this->gp['formtime']) > $maxTime) {
			$spam = TRUE;
		}
		return $spam;
	}

}
?>
