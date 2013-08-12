<?php
/**
 * ext tables config file for ext: "formhandler"
 *
 * @author Reinhard Führicht <rf@typoheads.at>

 * @package	Tx_Formhandler
 */

if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {

	// dynamic flexform
	include_once(t3lib_extMgm::extPath($_EXTKEY) . '/Resources/PHP/class.tx_dynaflex.php');

	t3lib_div::loadTCA('tt_content');

	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key,pages';

	// Add flexform field to plugin options
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

	$file = 'FILE:EXT:' . $_EXTKEY . '/Resources/XML/flexform_ds.xml';

	// Add flexform DataStructure
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', $file);

	t3lib_extMgm::addModule('web', 'txformhandlermoduleM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Controller/Module/');
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_formhandler_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'Resources/PHP/class.tx_formhandler_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/Settings/', 'Example Configuration');
t3lib_extMgm::addPlugin(array('Formhandler', $_EXTKEY . '_pi1'), 'list_type');

$TCA['tx_formhandler_log'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log',
		'label' => 'uid',
		'default_sortby' => 'ORDER BY crdate DESC',
		'crdate' => 'crdate',
		'tstamp' => 'tstamp',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'adminOnly' => 1
	)
);
t3lib_extMgm::allowTableOnStandardPages('tx_formhandler_log');

$TCA['pages']['columns']['module']['config']['items'][] = array(
	'LLL:EXT:' . $_EXTKEY . '/Resources/Language/locallang.xml:title',
	'formlogs',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
);
t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-formlogs', t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Images/pagetreeicon.png');

?>