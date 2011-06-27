<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_UtilityFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxRemoveFile {

	public function main() {
		$this->init();
		$content = '';

		if ($this->fieldName) {
			$sessionFiles = $this->globals->getSession()->get('files');
			if (is_array($sessionFiles)) {
				foreach ($sessionFiles as $field => $files) {

					if (!strcmp($field, $this->fieldName)) {
						$found = FALSE;
						foreach ($files as $key=>&$fileInfo) {
							if (!strcmp($fileInfo['uploaded_name'], $this->uploadedFileName)) {
								$found = TRUE;
								unset($sessionFiles[$field][$key]);
							}
						}
						if (!$found) {
							foreach ($files as $key=>&$fileInfo) {
								if (!strcmp($fileInfo['name'], $this->uploadedFileName)) {
									$found = TRUE;
									unset($sessionFiles[$field][$key]);
								}
							}
						}
					}
				}
			}

			$this->globals->getSession()->set('files', $sessionFiles);

			// Add the content to or Result Box: #formResult
			if (is_array($sessionFiles) && !empty($sessionFiles[$field])) {
				$markers = array();
				$view = $this->componentManager->getComponent('Tx_Formhandler_View_Form');
				$view->setSettings($this->settings);
				$view->fillFileMarkers($markers);
				$langMarkers = $this->utilityFuncs->getFilledLangMarkers($markers['###'. $this->fieldName . '_uploadedFiles###'], $this->langFiles);
				$markers['###'. $this->fieldName . '_uploadedFiles###'] = $this->globals->getCObj()->substituteMarkerArray($markers['###'. $this->fieldName . '_uploadedFiles###'], $langMarkers);
				$content = $markers['###'. $this->fieldName . '_uploadedFiles###'];
			}
		}
		print $content;
	}

	protected function init() {
		$this->fieldName = $_GET['field'];
		$this->uploadedFileName = $_GET['uploadedFileName'];
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
		$randomID = t3lib_div::_GP('randomID');
		$this->globals->setRandomID($randomID);
		
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

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxRemoveFile');
$output->main();

?>
