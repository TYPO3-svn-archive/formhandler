<?php
/**
 * ext tables config file for ext: "formhandler"
 *
 * @author Reinhard Führicht <rf@typoheads.at>

 * @package	Tx_Formhandler
 */
 
 /**
	\mainpage 	
	
	 @version V1.0.0 Beta

	Released under the terms of the GNU General Public License version 2 as published by
	the Free Software Foundation.
	
	The swiss army knife for all kinds of mailforms, completely new written using the MVC concept. 
	Result: Flexibility, Flexibility, Flexibility. Formhandler is a total redesign of the getting-old
	MailformPlus (aka th_mailformplus). Formhandler has now a new core, new architecture, new features.

	Beside the reach set of features provided by Formhandler, you may like the flexibility in the sense
	of possible different configuration. Projects have all their own specificities. One customer want this 
	component while the other one want to have this other one. I think it is very challenging to come up 
	with an extension that is features reach without overloading the code basis.
	
	Formhandler solves the problem by having a very modular approach. The extension is piloted 
	mainly by some nice TypoScript where is is possible to define exactly what to implement. You may
	want to play with some interceptor, finisher, logger, validators etc... For more information,
	you should have a look into the folder "Examples" of the extension which refers many interesting samples.
		
	Latest development version on
	http://forge.typo3.org/repositories/show/extension-formhandler
	  
 */

if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE == 'BE')   {

	# dynamic flexform
	include_once(t3lib_extMgm::extPath($_EXTKEY) . '/Resources/PHP/class.tx_dynaflex.php');
	
	t3lib_div::loadTCA('tt_content');
	
	// Add flexform field to plugin options
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . ''] = 'pi_flexform';
	
	if(!is_object($GLOBALS['BE_USER'])) {
		$GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_beUserAuth');
		
		// New backend user object
		$GLOBALS['BE_USER']->start(); // Object is initialized
		$GLOBALS['BE_USER']->checkCLIuser();
		$GLOBALS['BE_USER']->backendCheckLogin(); 
		$GLOBALS['BE_USER']->fetchGroupData();
	}
	
	$file = 'FILE:EXT:' . $_EXTKEY . '/Resources/XML/flexform_ds.xml';

	$tsConfig = t3lib_BEfunc::getModTSconfig(0, 'plugin.Tx_Formhandler');
	$tsConfig = $tsConfig['properties'];
	if($tsConfig['flexformFile']) {
		$file = $tsConfig['flexformFile'];
	}
	
	// Add flexform DataStructure
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '', $file);

	t3lib_extMgm::addModule('web', 'txformhandlermoduleM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Controller/Module/');
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_formhandler_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'Resources/PHP/class.tx_formhandler_wizicon.php';
} elseif($GLOBALS['TSFE']->id) {

	/* We don't need the static template to get formhandler working since we do addPItoST43
	 * 
	 * $sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
	
	if(!$GLOBALS['TSFE']->sys_page) {
		$GLOBALS['TSFE']->sys_page = $sysPageObj;
	}
	
	$rootLine = $sysPageObj->getRootLine($GLOBALS['TSFE']->id);
	$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
	$TSObj->tt_track = 0;
	$TSObj->init();
	$TSObj->runThroughTemplates($rootLine);
	$TSObj->generateConfig();
	if(!$TSObj->setup['plugin.']['Tx_Formhandler.']['userFunc']) {
		t3lib_div::debug('No static template found! Make sure to include "Settings (formhandler)" in your TypoScript template!');
	} */

}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/Settings/', 'Default Predefined Form');
t3lib_extMgm::addPlugin(array('Formhandler', $_EXTKEY), 'list_type');
t3lib_extMgm::addPItoST43('formhandler');

t3lib_extMgm::addPlugin(array('Formhandler Listing', $_EXTKEY . '_pi2'), 'list_type'); 
?>