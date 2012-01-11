<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxValidate {

	public function main() {
		$this->init();
		if ($this->fieldname) {
			$this->globals->setCObj($GLOBALS['TSFE']->cObj);
			$randomID = htmlspecialchars(t3lib_div::_GP('randomID'));
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
			$valid = $validator->validateAjax($this->fieldname, $this->value, $errors);
			$this->settings = $this->globals->getSession()->get('settings');
			$content = '';
			if ($valid) {
				
				$content = $this->utilityFuncs->getSingle($this->settings['ajax.']['config.'], 'ok');
				if(strlen($content) === 0) {
					$content = '<img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ok.png' . '" />';
				} else {
					$gp = array(
						$_GET['field'] => $_GET['value']
					);
					$view = $this->initView($content);
					$content = $view->render($gp);
					$content = '<span class="success">' . $content . '</span>';
				}
			} else {
				$content = $this->utilityFuncs->getSingle($this->settings['ajax.']['config.'], 'notOk');
				if(strlen($content) === 0) {
					$content = '<img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/notok.png' . '" />';
				} else {
					$view = $this->initView($content);
					$gp = array(
						$_GET['field'] => $_GET['value']
					);
					$content = $view->render($gp, $errors);
					$content = '<span class="error">' . $content . '</span>';
				}
			}
			print $content;
		}
	}

	protected function init() {
		$this->fieldname = htmlspecialchars($_GET['field']);
		$this->value = htmlspecialchars($_GET['value']);
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

	protected function initView($content) {
		$viewClass = 'Tx_Formhandler_View_AjaxValidation';
		$view = $this->componentManager->getComponent($viewClass);
		$view->setLangFiles($this->utilityFuncs->readLanguageFiles(array(), $this->settings));
		$view->setSettings($this->settings);
		$templateName = 'AJAX';
		$template = str_replace('###fieldname###', htmlspecialchars($_GET['field']), $content);
		$template = '###TEMPLATE_' . $templateName . '###' . $template . '###TEMPLATE_' . $templateName . '###';
		$view->setTemplate($template, 'AJAX');
		return $view;
	}

}

$validator = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxValidate');
$validator->main();

?>