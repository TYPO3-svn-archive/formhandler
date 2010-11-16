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
 * @package	Tx_Formhandler
 * @subpackage	Controller
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
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;

	}
	
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * init method to load translation data and set log table.
	 *
	 * @global $LANG
	 * @return void
	 */
	protected function init() {
		global $LANG;
		$LANG->includeLLFile('EXT:formhandler/Resources/Language/locallang.xml');
		$templatePath = t3lib_extMgm::extPath('formhandler') . 'Resources/HTML/backend/';
		$templateFile = $templatePath . 'template.html';
		$this->templateCode = t3lib_div::getURL($templateFile);
	}

	/**
	 * Main method of the controller.
	 *
	 * @return string rendered view
	 */
	public function process() {
		
		//init
		$this->init();

		//init gp params
		$params = t3lib_div::_GP('formhandler');
		
		if (isset($params['clearTables']) && is_array($params['clearTables'])) {
			$this->clearTables($params['clearTables']);
		}

		return $this->getOverview();
	}
	
	/**
	 * Truncates tables.
	 *
	 * @param array The names of the tables to truncate
	 * @return void
	 */
	protected function clearTables($tablesArray) {
		foreach ($tablesArray as $table) {
			$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE ' . $table);
		}
	}
	
	/**
	 * Returns HTML code for an overview table showing all found tables and how many rows are in them.
	 *
	 * @global $LANG
	 * @return string
	 */
	protected function getOverview() {
		global $LANG;
		$existingTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		$code = Tx_Formhandler_StaticFuncs::getSubpart($this->templateCode, '###CLEAR_LOGS###');
		$markers = array();
		$markers['###URL###'] = $_SERVER['PHP_SELF'];
		$markers['###UID###'] = $this->id;
		$markers['###LLL:table###'] = $LANG->getLL('table');
		$markers['###LLL:total_rows###'] = $LANG->getLL('total_rows');
		
		$markers['###TABLES###'] = '';
		foreach ($existingTables as $table => $tableSettings) {
			
			if (strpos($table, 'tx_formhandler_') > -1) {
				$res = $GLOBALS['TYPO3_DB']->sql_query('SELECT COUNT(*) as rowCount FROM ' . $table);
				if ($res) {
					$rowCode = Tx_Formhandler_StaticFuncs::getSubpart($this->templateCode, '###CLEAR_LOGS_TABLE###');
					$tableMarkers = array();
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$tableMarkers['###TABLE###'] = $table;
					$tableMarkers['###ROW_COUNT###'] = $row['rowCount'];
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
					$markers['###TABLES###'] .= Tx_Formhandler_StaticFuncs::substituteMarkerArray($rowCode, $tableMarkers);
				}
				
			}
			
		}
		$markers['###LLL:clear###'] = $LANG->getLL('clear_selected_tables');
		return Tx_Formhandler_StaticFuncs::substituteMarkerArray($code, $markers);
	}

}
?>
