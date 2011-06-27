<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxValidate {

	public function main() {
		$this->init();
		if ($this->fieldname) {
			$this->globals->setCObj($GLOBALS['TSFE']->cObj);
			$randomID = t3lib_div::_GP('randomID');
			$this->globals->setRandomID($randomID);
			$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
			if(!$this->globals->getSession()) {
				$ts = $GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.']['settings.'];
				$sessionClass = 'Tx_Formhandler_Session_PHP';
				if($ts['session.']) {
					$sessionClass = $this->utilityFuncs->prepareClassName($ts['session.']['class']);
				}
				$this->globals->setSession($this->componentManager->getComponent($sessionClass));
			}
			$validator = $this->componentManager->getComponent('Tx_Formhandler_Validator_Ajax');
			print $validator->validateAjax($this->fieldname, $this->value);
		}
	}

	protected function init() {
		$this->fieldname = $_GET['field'];
		$this->value = $_GET['value'];
		if (isset($_GET['pid'])) {
			$this->id = intval($_GET['pid']);
		} else {
			$this->id = intval($_GET['id']);
		}
		tslib_eidtools::connectDB();
		$this->globals = Tx_Formhandler_Globals::getInstance();
		$this->utilityFuncs = Tx_Formhandler_UtilityFuncs::getInstance();
		$this->utilityFuncs->initializeTSFE($this->id);
	}


}

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxValidate');
$output->main();

?>