<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_StaticFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxValidate {

	public function main() {
		$this->init();
		if ($this->fieldname) {
			Tx_Formhandler_Globals::$cObj = $GLOBALS['TSFE']->cObj;
			$randomID = t3lib_div::_GP('randomID');
			Tx_Formhandler_Globals::$randomID = $randomID;
			$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
			if(!Tx_Formhandler_Globals::$session) {
				$ts = $GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.']['settings.'];
				$sessionClass = 'Tx_Formhandler_Session_PHP';
				if($ts['session.']) {
					$sessionClass = Tx_Formhandler_StaticFuncs::prepareClassName($ts['session.']['class']);
				}
				Tx_Formhandler_Globals::$session = $this->componentManager->getComponent($sessionClass);
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
		Tx_Formhandler_StaticFuncs::initializeTSFE($this->id);
	}


}

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxValidate');
$output->main();

?>