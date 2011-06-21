<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_StaticFuncs.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxRemoveFile {

	public function main() {
		$this->init();
		$content = '';

		if ($this->fieldName) {
			$sessionFiles = Tx_Formhandler_Globals::$session->get('files');
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

			Tx_Formhandler_Globals::$session->set('files', $sessionFiles);

			// Add the content to or Result Box: #formResult
			if (is_array($sessionFiles) && !empty($sessionFiles[$field])) {
				$markers = array();
				$view = $this->componentManager->getComponent('Tx_Formhandler_View_Form');
				$view->setSettings($this->settings);
				$view->fillFileMarkers($markers);
				$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($markers['###'. $this->fieldName . '_uploadedFiles###'], $this->langFiles);
				$markers['###'. $this->fieldName . '_uploadedFiles###'] = Tx_Formhandler_Globals::$cObj->substituteMarkerArray($markers['###'. $this->fieldName . '_uploadedFiles###'], $langMarkers);
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
		tslib_eidtools::connectDB();
		Tx_Formhandler_StaticFuncs::initializeTSFE($this->id);
		Tx_Formhandler_Globals::$cObj = $GLOBALS['TSFE']->cObj;
		$randomID = t3lib_div::_GP('randomID');
		Tx_Formhandler_Globals::$randomID = $randomID;
		
		if(!Tx_Formhandler_Globals::$session) {
			$ts = $GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.']['settings.'];
			$sessionClass = 'Tx_Formhandler_Session_PHP';
			if($ts['session.']) {
				$sessionClass = Tx_Formhandler_StaticFuncs::prepareClassName($ts['session.']['class']);
			}
			Tx_Formhandler_Globals::$session = $this->componentManager->getComponent($sessionClass);
		}
		
		$this->settings = Tx_Formhandler_Globals::$session->get('settings');
		$this->langFiles = Tx_Formhandler_StaticFuncs::readLanguageFiles(array(), $this->settings);

		//init ajax
		if ($this->settings['ajax.']) {
			$class = $this->settings['ajax.']['class'];
			if (!$class) {
				$class = 'Tx_Formhandler_AjaxHandler_JQuery';
			}
			$class = Tx_Formhandler_StaticFuncs::prepareClassName($class);
			$ajaxHandler = $this->componentManager->getComponent($class);
			Tx_Formhandler_Globals::$ajaxHandler = $ajaxHandler;
			
			$ajaxHandler->init($this->settings['ajax.']['config.']);
			$ajaxHandler->initAjax();
		}
	}

}

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxRemoveFile');
$output->main();

?>
