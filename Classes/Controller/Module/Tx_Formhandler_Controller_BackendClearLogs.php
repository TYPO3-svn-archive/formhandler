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

/**
 * Controller for Backend Module of Formhandler handling the "clear log" option
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_Controller_BackendClearLogs extends Tx_Formhandler_AbstractController {

	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
	 */
	protected $componentManager;

	/**
	 * The global Formhandler configuration
	 *
	 * @access protected
	 * @var Tx_Formhandler_Configuration
	 */
	protected $configuration;


	/**
	 * The constructor for a finisher setting the component manager and the configuration.
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration, Tx_Formhandler_UtilityFuncs $utilityFuncs) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->utilityFuncs = $utilityFuncs;
	}

	/**
	 * Sets the given ID as the current page ID.
	 *
	 * @param int $id
	 * @return void
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * init method to load translation data and set log table.
	 *
	 * @global $GLOBALS['LANG']
	 * @return void
	 */
	protected function init() {
		$tsconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($this->id, 'tx_formhandler_mod1');
		$this->settings = $tsconfig['properties']['config.'];

		$GLOBALS['LANG']->includeLLFile('EXT:formhandler/Resources/Language/locallang.xml');
		$templatePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('formhandler') . 'Resources/HTML/backend/';
		$templateFile = $templatePath . 'template.html';
		$this->templateCode = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($templateFile);
	}

	/**
	 * Main method of the controller.
	 *
	 * @return string rendered view
	 */
	public function process() {
		$content = '';

		//init
		$this->init();

		if(intval($this->settings['enableClearLogs']) !== 1 && !$GLOBALS['BE_USER']->user['admin']) {
			return;
		}

		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('COUNT(*) as rowCount', 'tx_formhandler_log', '1=1');
		$rowCount = $row['rowCount'];

		//init gp params
		$params = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('formhandler');
		if (isset($params['doDelete']) && intval($params['doDelete']) === 1) {
			$messageHeader = $GLOBALS['LANG']->getLL('clear-logs-success-header');
			$messageText = sprintf($GLOBALS['LANG']->getLL('clear-logs-success-message'), intval($rowCount));
			$message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Core\Messaging\FlashMessage', $messageText, $messageHeader);
			$content = $message->render();
			$this->clearTables();
			$rowCount = 0;
		}

		$content .= $this->getOverview($rowCount);
		return $content;
	}

	/**
	 * Truncates tables.
	 *
	 * @param array The names of the tables to truncate
	 * @return void
	 */
	protected function clearTables() {
		$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE tx_formhandler_log');
	}

	/**
	 * Returns HTML code for an overview table showing all found tables and how many rows are in them.
	 *
	 * @global $GLOBALS['LANG']
	 * @return string
	 */
	protected function getOverview($rowCount) {
		$code = $this->utilityFuncs->getSubpart($this->templateCode, '###CLEAR_LOGS###');
		$markers = array();
		$markers['###URL###'] = $_SERVER['PHP_SELF'];
		$markers['###UID###'] = $this->id;

		$markers['###TABLES###'] = '';
		if($rowCount > 0) {
			$markers['###LLL:clear-logs-message###'] = sprintf($GLOBALS['LANG']->getLL('clear-logs-message'), intval($rowCount));
			$markers['###LLL:clear###'] = $GLOBALS['LANG']->getLL('clear');
		} else {
			$code = $this->utilityFuncs->getSubpart($this->templateCode, '###NO_LOGS###');
			$markers['###LLL:clear-logs-message###'] = $GLOBALS['LANG']->getLL('no-logs-message');
		}
		
		return $this->utilityFuncs->substituteMarkerArray($code, $markers);
	}

}
?>
