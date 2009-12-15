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
 *                                                                        */


require_once(PATH_t3lib."class.t3lib_page.php");
require_once(PATH_t3lib."class.t3lib_tsparser_ext.php");

/**
 * Flexform class for Formhandler spcific needs
 *
 * @author Thomas Hempel <thomas@typo3-unleashed.net>
 * @author Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Resources
 */
class tx_dynaflex_formhandler {

	/**
	 * Adds onchange listener on the drop down menu "predefined".
	 * If the event is fired and old value was ".default", then empty some fields.
	 *
	 * @param array $config
	 * @return string the javascript
	 * @author Fabien Udriot
	 */
	function addFields_predefinedJS($config) {
		$newRecord = 'true';
		if ($config['row']['pi_flexform'] != '') {
			$flexData = t3lib_div::xml2array($config['row']['pi_flexform']);
			if (isset($flexData['data']['sDEF']['lDEF']['predefined'])) {
				$newRecord = 'false';
			}
		}
		$uid = key($GLOBALS['SOBE']->editconf['tt_content']);
		if($uid < 0 || empty($uid) || !strstr($uid,'NEW')) {
			$uid = $GLOBALS['SOBE']->elementsData[0]['uid'];
		}
		//print_r($GLOBALS['SOBE']->elementsData[0]);
		
		$js = "<script>\n";
		$js .= "/*<![CDATA[*/\n";
		
		$divId = $GLOBALS['SOBE']->tceforms->dynNestedStack[0][1];
		if(!$divId) {
			//$divId = 'DTM-' . $uid;
			$divId = "DIV.c-tablayer";
		} else {
			$divId .= "-DIV";
		}
		$js .= "var uid = '" . $uid . "'\n";
		$js .= "var flexformBoxId = '" . $divId . "'\n";
		//$js .= "var flexformBoxId = 'DIV.c-tablayer'\n";
		$js .= "var newRecord = " . $newRecord . "\n";
		$js .= file_get_contents(t3lib_extMgm::extPath('formhandler') . 'Resources/JS/addFields_predefinedJS.js');
		$js .= "/*]]>*/\n";
		$js .= "</script>\n";
		return $js;
	}

	/**
	 * Sets the items for the "Predefined" dropdown.
	 *
	 * @param array $config
	 * @return array The config including the items for the dropdown
	 */
	function addFields_predefined ($config) {

		global $LANG;
		
		$pid = $config['row']['pid'];
		if($pid < 0) {
			$contentUid = str_replace('-','',$pid);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pid','tt_content','uid='.$contentUid);
			if($res) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$pid = $row['pid'];
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
			}
		}
		$ts = $this->loadTS($pid);
		
		$predef = array();

		# no config available
		if (!is_array($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.']) || sizeof($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.']) == 0) {
			$optionList[] = array(0 => $LANG->sL('LLL:EXT:formhandler/Resources/Language/locallang_db.xml:be_missing_config'), 1 => '');
			return $config['items'] = array_merge($config['items'],$optionList);
		}

		# for each view
		foreach($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.'] as $key=>$view) {

			if (is_array($view)) {
				$beName = $view['name'];
				if (!$predef[$key]) $predef[$key] = $beName;
			}
		}
		
		$optionList = array();
		$optionList[] = array(0 => $LANG->sL('LLL:EXT:formhandler/Resources/Language/locallang_db.xml:be_please_select'), 1 => '');
		foreach($predef as $k => $v) {
			$optionList[] = array(0 => $v, 1 => $k);
		}
		$config['items'] = array_merge($config['items'],$optionList);
		return $config;
	}

	/**
	 * Loads the TypoScript for the current page
	 *
	 * @param int $pageUid
	 * @return array The TypoScript setup
	 */
	function loadTS($pageUid) {
		$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pageUid);
		$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
		return $TSObj->setup;
	}
}

?>
