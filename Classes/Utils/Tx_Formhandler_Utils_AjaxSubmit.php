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
*                                                                        */

/**
 * A class calling the controller and returning the form content as JSON. This class is called via AJAX.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxSubmit {

	/**
	 * Main method of the class.
	 *
	 * @return string The HTML list of remaining files to be displayed in the form
	 */
	public function main() {
		$this->init();
		$content = '';

		$settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_formhandler_pi1.'];
		$settings['usePredef'] = $this->globals->getSession()->get('predef');
		
		$content = $GLOBALS['TSFE']->cObj->cObjGetSingle('USER', $settings);

		$content = '{' . json_encode('form') . ':' . json_encode($content) . '}';
		print $content;
	}

	/**
	 * Initialize the class. Read GET parameters
	 *
	 * @return void
	 */
	protected function init() {
		if (isset($_GET['pid'])) {
			$this->id = intval($_GET['pid']);
		} else {
			$this->id = intval($_GET['id']);
		}
		
		$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
		$this->globals = Tx_Formhandler_Globals::getInstance();
		$this->utilityFuncs = Tx_Formhandler_UtilityFuncs::getInstance();
		tslib_eidtools::connectDB();
		$this->utilityFuncs->initializeTSFE($this->id);
		$this->globals->setCObj($GLOBALS['TSFE']->cObj);
		$randomID = htmlspecialchars(t3lib_div::_GP('randomID'));
		$this->globals->setRandomID($randomID);
		$this->globals->setAjaxMode(TRUE);
		if(!$this->globals->getSession()) {
			$ts = $GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.']['settings.'];
			$sessionClass = 'Tx_Formhandler_Session_PHP';
			if($ts['session.']) {
				$sessionClass = $this->utilityFuncs->prepareClassName($ts['session.']['class']);
			}
			$this->globals->setSession($this->componentManager->getComponent($sessionClass));
		}
		
		$this->settings = $this->globals->getSession()->get('settings');
		$this->langFiles = $this->utilityFuncs->readLanguageFiles(array(), $this->settings);

		//init ajax
		if ($this->settings['ajax.']) {
			$class = $this->settings['ajax.']['class'];
			if (!$class) {
				$class = 'Tx_Formhandler_AjaxHandler_JQuery';
			}
			$class = $this->utilityFuncs->prepareClassName($class);
			$ajaxHandler = $this->componentManager->getComponent($class);
			$this->globals->setAjaxHandler($ajaxHandler);

			$ajaxHandler->init($this->settings['ajax.']['config.']);
			$ajaxHandler->initAjax();
		}
	}

}

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxSubmit');
$output->main();

?>