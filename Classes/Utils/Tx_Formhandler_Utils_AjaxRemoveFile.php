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
		$this->initializeTSFE($this->id);
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

	protected function initializeTSFE($pid, $feUserObj = '') {
		global $TSFE, $TYPO3_CONF_VARS;

			// include necessary classes:
			// Note: BEfunc is needed from t3lib_tstemplate
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_befunc.php');
		require_once(PATH_tslib . 'class.tslib_fe.php');
		require_once(PATH_t3lib . 'class.t3lib_userauth.php');
		require_once(PATH_tslib . 'class.tslib_feuserauth.php');
		require_once(PATH_tslib . 'class.tslib_content.php');
		require_once(PATH_tslib . 'class.tslib_fe.php');

			// create object instances:
		$TSFE = t3lib_div::makeInstance('tslib_fe', $TYPO3_CONF_VARS, $pid, 0, TRUE);

		$TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$TSFE->tmpl = t3lib_div::makeInstance('t3lib_tstemplate');
		$TSFE->tmpl->init();

			// fetch rootline and extract ts setup:
		$TSFE->rootLine = $TSFE->sys_page->getRootLine(intval($pid));
		$TSFE->getConfigArray();

			// then initialize fe user
		$TSFE->initFEuser();
		$TSFE->fe_user->fetchGroupData();

			// initialize the backend user
		//$this->initializeBackendUser();

			// Include the TCA
		$TSFE->includeTCA();

			// Get the page
		$TSFE->fetch_the_id();
		$TSFE->getPageAndRootline();
		$TSFE->initTemplate();
		$TSFE->tmpl->getFileName_backPath = PATH_site;
		$TSFE->forceTemplateParsing = TRUE;
		$TSFE->getConfigArray();
		$TSFE->newCObj();

			// Get the Typoscript as its inherited from parent pages
		$template = t3lib_div::makeInstance('t3lib_tsparser_ext'); // Defined global here!
		$template->init();

		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($pid);
		$template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.

		$template->generateConfig();

			// Save the setup
		$this->setup = $template->setup;
	}
	
	protected function initializeBackendUser() {
		global $BE_USER, $TYPO3_DB, $TSFE, $LANG;

		if ($this->initBE) {
			return;
		}
		$this->initBE = TRUE;

		$GLOBALS['BE_USER'] = NULL;

			// If the backend cookie is set, we proceed and check if a backend user is logged in.
		if ($_COOKIE['be_typo_user']) {
			require_once (PATH_t3lib . 'class.t3lib_befunc.php');
			require_once (PATH_t3lib . 'class.t3lib_userauthgroup.php');
			require_once (PATH_t3lib . 'class.t3lib_beuserauth.php');
			require_once (PATH_t3lib . 'class.t3lib_tsfebeuserauth.php');

			$GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
			$GLOBALS['BE_USER']->OS = TYPO3_OS;
			$GLOBALS['BE_USER']->lockIP = $GLOBALS['TYPO3_CONF_VARS']['BE']['lockIP'];
			$GLOBALS['BE_USER']->start();
			$GLOBALS['BE_USER']->unpack_uc('');
			if ($GLOBALS['BE_USER']->user['uid']) {
				$GLOBALS['BE_USER']->fetchGroupData();
				$GLOBALS['TSFE']->beUserLogin = TRUE;
			}
			if ($GLOBALS['BE_USER']->checkLockToIP() && $GLOBALS['BE_USER']->checkBackendAccessSettingsFromInitPhp() && $GLOBALS['BE_USER']->user['uid']) {
				$GLOBALS['BE_USER']->initializeAdminPanel();
				$GLOBALS['BE_USER']->initializeFrontendEdit();
			} else {
				$GLOBALS['BE_USER'] = '';
				$GLOBALS['TSFE']->beUserLogin = FALSE;
			}
		}

		require_once(t3lib_extMgm::extPath('lang') . 'lang.php');
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
	}
}

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxRemoveFile');
$output->main();

?>
