<?php
/**
 * index.php for the backend module of ext: "formhandler"
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008  <rf@typoheads.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($GLOBALS['BACK_PATH'] . 'init.php');

$GLOBALS['LANG']->includeLLFile('EXT:formhandler/Resources/Language/locallang.xml');

$GLOBALS['BE_USER']->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

/**
 * Module 'Formhandler' for the 'formhandler' extension.
 *
 * @author	 Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */
class tx_formhandler_module1 extends \TYPO3\CMS\Backend\Module\BaseScriptClass {
	var $pageinfo;

	/**
	 * Array holding the TypoScript set in userTS or pageTS.
	 * 
	 * @access private
	 * @var array
	 */
	private $settings;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		$id = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id'));
		$tsconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($id, 'tx_formhandler_mod1');
		$this->settings = $tsconfig['properties']['config.'];
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->MOD_MENU = array (
			'function' => array (
				'1' => $GLOBALS['LANG']->getLL('function1')
			)
		);
		if(intval($this->settings['enableClearLogs']) === 1 || $GLOBALS['BE_USER']->user['admin']) {
			$this->MOD_MENU['function']['2'] = $GLOBALS['LANG']->getLL('function2');
		}
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($GLOBALS['BE_USER']->user['admin'] && !$this->id))	{

			// Draw the header.
			$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('mediumDoc');
			$this->doc->backPath = $GLOBALS['BACK_PATH'];

			/** @var $pageRenderer \TYPO3\CMS\Core\Page\PageRenderer */
			$pageRenderer = $this->doc->getPageRenderer();
			$pageRenderer->loadExtJS();
			$pageRenderer->addJsFile($GLOBALS['BACK_PATH'] . '../typo3/sysext/backend/Resources/Public/JavaScript/tceforms.js');
			$pageRenderer->addJsFile($GLOBALS['BACK_PATH'] . '../typo3/js/extjs/ux/Ext.ux.DateTimePicker.js');

			// Define settings for Date Picker
			$typo3Settings = array(
				'datePickerUSmode' => 0,
				'dateFormat' => array('j.n.Y', 'j.n.Y G:i')
			);
			$pageRenderer->addInlineSettingArray('', $typo3Settings);

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			//$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('', $this->doc->funcMenu('', \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'], basename(PATH_thisScript))));
			$this->content .= $this->doc->divider(5);
			$this->content .= $this->moduleContent();

			// ShortCut
			if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
				$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}

			$this->content .= $this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('mediumDoc');
			$this->doc->backPath = $GLOBALS['BACK_PATH'];

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $GLOBALS['LANG']->getLL('no_id');
			$this->content .= $this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{

		switch ((string)$this->MOD_SETTINGS['function']) {
			case 1:
				// Render content:
				$componentManager = Tx_Formhandler_Component_Manager::getInstance();
				$controllerClass = 'Tx_Formhandler_Controller_Backend';
				$controller = $componentManager->getComponent($controllerClass);
				$controller->setId($this->id);
				$content = $controller->process();
				$this->content .= $this->doc->section('', $content, 0, 1);
				break;
			case 2:
				// Render content:
				$componentManager = Tx_Formhandler_Component_Manager::getInstance();
				$controllerClass = 'Tx_Formhandler_Controller_BackendClearLogs';
				$controller = $componentManager->getComponent($controllerClass);
				$controller->setId($this->id);
				$content = $controller->process();
				$this->content .= $this->doc->section('', $content, 0, 1);
				break;
			case 3:
				$content='<div align=center><strong>Menu item #3...</strong></div>';
				$this->content .= $this->doc->section('Message #3:', $content, 0, 1);
				break;
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/formhandler/Classes/Controller/Module/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/formhandler/Classes/Controller/Module/index.php']);
}




// Make instance:
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_formhandler_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>