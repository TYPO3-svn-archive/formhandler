<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxSubmit {

	public function main() {
		$this->init();
		$content = '';

		$settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_formhandler_pi1.'];
		$settings['usePredef'] = $this->globals->getSession()->get('predef');
		
		$content = $GLOBALS['TSFE']->cObj->cObjGetSingle('USER', $settings);

		$content = '{' . json_encode('form') . ':' . json_encode($content) . '}';
		print $content;
	}

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