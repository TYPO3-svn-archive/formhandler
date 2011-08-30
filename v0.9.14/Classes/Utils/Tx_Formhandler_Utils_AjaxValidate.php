<?php

require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

class Tx_Formhandler_Utils_AjaxValidate {

	public function main() {
		$this->init();
		if ($this->fieldname) {
			$randomID = t3lib_div::_GP('randomID');
			Tx_Formhandler_Globals::$randomID = $randomID;
			$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
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
		$this->initializeTSFE($this->id);
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

$output = t3lib_div::makeInstance('Tx_Formhandler_Utils_AjaxValidate');
$output->main();

?>