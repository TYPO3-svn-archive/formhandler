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
*                                                                        */

/**
 * A class validating a field via AJAX.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxValidate {

	/**
	 * Main method of the class.
	 *
	 * @return string The HTML list of remaining files to be displayed in the form
	 */
	public function main() {
		$this->init();
		if ($this->fieldname) {
			$this->globals->setCObj($GLOBALS['TSFE']->cObj);
			$randomID = htmlspecialchars(t3lib_div::_GP('randomID'));
			$this->globals->setRandomID($randomID);
			$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
			if(!$this->globals->getSession()) {
				$ts = $GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.']['settings.'];
				$sessionClass = $this->utilityFuncs->getPreparedClassName($ts['session.'], 'Session_PHP');
				$this->globals->setSession($this->componentManager->getComponent($sessionClass));
			}
			$validator = $this->componentManager->getComponent('Tx_Formhandler_Validator_Ajax');
			$errors = array();
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
					$content = $view->render($gp, $errors);
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

	/**
	 * Initialize the class. Read GET parameters
	 *
	 * @return void
	 */
	protected function init() {
		$this->fieldname = htmlspecialchars(stripslashes($_GET['field']));
		$this->value = htmlspecialchars(stripslashes($_GET['value']));
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

	/**
	 * Initialize the AJAX validation view.
	 *
	 * @param string $content The raw content
	 * @return Tx_Formhandler_View_AjaxValidation The view class
	 */
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