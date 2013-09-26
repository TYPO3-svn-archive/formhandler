<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_CompatibilityFuncs.php');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_formhandler_pi1.php', '_pi1', 'list_type', 0);

$compatFuncs = Tx_Formhandler_CompatibilityFuncs::getInstance();

//Hook in tslib_content->stdWrap
if($compatFuncs->convertVersionNumberToInteger(TYPO3_version) >= 6000000) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] = 'EXT:formhandler/Resources/PHP/Hooks/class.tx_formhandler_stdwrap.php:tx_formhandler_stdwrapHook';
} else {
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] = 'EXT:formhandler/Resources/PHP/Hooks/class.tx_formhandler_stdwrap_4x.php:tx_formhandler_stdwrap';
}

//Delete cache file on "clear cache" command
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'EXT:formhandler/Resources/PHP/Hooks/class.tx_formhandler_clearCache.php:tx_formhandler_clearCache->clearCache';

$TYPO3_CONF_VARS['FE']['eID_include']['formhandler'] = 'EXT:formhandler/Classes/Utils/Tx_Formhandler_Utils_AjaxValidate.php';
$TYPO3_CONF_VARS['FE']['eID_include']['formhandler-removefile'] = 'EXT:formhandler/Classes/Utils/Tx_Formhandler_Utils_AjaxRemoveFile.php';
$TYPO3_CONF_VARS['FE']['eID_include']['formhandler-ajaxsubmit'] = 'EXT:formhandler/Classes/Utils/Tx_Formhandler_Utils_AjaxSubmit.php';
?>