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
 *
 * $Id$
 *                                                                        */

/**
 * Frontend listing controller for Formhandler.
 *
 * Example TypoScript:
 *
 * <code>
 *
 * # Currently I haven't found a better solution
 * includeLibs.Tx_Formhandler_FEListing = EXT:formhandler/Classes/Controller/tx_Formhandler_Dispatcher.php
 * plugin.Tx_Formhandler_FEListing = USER_INT
 * plugin.Tx_Formhandler_FEListing.userFunc = tx_Formhandler_Dispatcher->main
 * tt_content.list.20.formhandler_pi2 < plugin.Tx_Formhandler_FEListing
 * tt_content.list.20.formhandler_pi2.controller = Tx_Formhandler_Controller_Listing
 *
 * #The view class to use. Default: Tx_Formhandler_View_Listing
 * plugin.Tx_Formhandler.settings.fe_listing.view = Tx_Formhandler_View_Listing
 * plugin.Tx_Formhandler.settings.fe_listing.templateFile = fileadmin/templates/ext/formhandler/listing.html
 *
 * #enter a page id or comma seperated list of page ids. Only records of this page(s) will be shown
 * plugin.Tx_Formhandler.settings.fe_listing.pid = 39
 *
 * #enter the db field name holding the page ID. Default: pid
 * plugin.Tx_Formhandler.settings.fe_listing.pidField = pid
 * plugin.Tx_Formhandler.settings.fe_listing.table = tt_content
 * plugin.Tx_Formhandler.settings.fe_listing.orderby = subheader DESC
 *
 * #if set, the marker ###DELETE### gets replaced by a link to delete the record
 * plugin.Tx_Formhandler.settings.fe_listing.enableDelete = 1
 *
 * #map db fields to form fields again. Use markers like ###value_name### in template
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.header = name
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.bodytext = subject
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.subheader = sub_datetime
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.crdate = sub_tstamp
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.tstamp = sub_tstamp
 * plugin.Tx_Formhandler.settings.fe_listing.mapping.imagecaption = ip
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */
class Tx_Formhandler_Controller_Listing extends Tx_Formhandler_AbstractController {

	/**
	 * The GimmeFive component manager
	 *
	 * @access protected
	 * @var Tx_GimmeFive_Component_Manager
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
	 * The template file to be used. Only if template file was defined via plugin record
	 *
	 * @access protected
	 * @var string
	 */
	protected $templateFile;

	//not used
	protected $piVars;

	/**
	 * The constructor setting the component manager and the configuration.
	 *
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 * @param Tx_GimmeFive_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_GimmeFive_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->initializeController();
	}

	/**
	 * Main method of the listing controller.
	 *
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 * @return rendered view
	 */
	public function process() {
		$this->gp = t3lib_div::_GP('formhandler');
		Tx_Formhandler_Globals::$gp = $this->gp;

		//read settings
		$settings = $this->configuration->getSettings();
		if(!$settings['fe_listing.']) {
			throw new Exception('no_config', 'Tx_Formhandler_Controller_Listing');
		}
		$settings = $settings['fe_listing.'];

		//read table
		$table = $settings['table'];

		if($this->gp['deleteId']) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, ('uid=' . $this->gp['deleteId']));
		}

		//set pid field
		$pidField = 'pid';
		if($settings['pidField']) {
			$pidField = $settings['pidField'];
		}
		$pids = t3lib_div::trimExplode(',', $settings['pid']);

		//parse mapping
		$this->getMapping($settings);

		//set template file
		$templateFile = $settings['templateFile'];
		if(isset($settings['templateFile.']) && is_array($settings['templateFile.'])) {
			$this->templateFile = Tx_Formhandler_StaticFuncs::getSingle($settings, 'templateFile');
		} else {
			$this->templateFile = t3lib_div::getURL(Tx_Formhandler_StaticFuncs::resolvePath($templateFile));
		}

		if(!$table || !$this->mapping) {
			throw new Exception('insufficient_config', 'Tx_Formhandler_Controller_Listing');
		}

		//set view
		$viewClass = $settings['view'];
		if(!$viewClass) {
			$viewClass = 'Tx_Formhandler_View_Listing';
		}
		$viewClass = Tx_Formhandler_StaticFuncs::prepareClassName($viewClass);

		$view = $this->componentManager->getComponent($viewClass);

		if($this->gp['detailId']) {
			$view->setTemplate($this->templateFile, 'DETAIL');
		} else {
			$view->setTemplate($this->templateFile, 'LIST');
		}


		//build WHERE clause
		if($pids) {
			$where = $pidField . ' IN (' . (implode(',', $pids)) . ')';
		}

		//set ORDER BY
		$orderby = $settings['orderby'];

		//Select records
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(('uid,' . implode(',', array_keys($this->mapping))), $table, $where, '', $orderby);

		//buid items array
		$listItems = array();
		if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			while(FALSE !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {

				if(!isset($this->gp['detailId'])) {
					array_push($listItems, $row);
				} elseif(intval($row['uid']) === intval($this->gp['detailId'])) {

					array_push($listItems, $row);
				}
			}
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		//render view
		$view->setModel($listItems);
		return $view->render($this->gp, array());


	}

	/**
	 * Function to parse the db field <-> marker name settings in TypoScript
	 *
	 * @param &$settings The settings
	 * @return array The parsed mapping
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function getMapping(&$settings) {
		if(!is_array($settings['mapping.'])) {
			return array();
		}
		$mapping = array();
		foreach($settings['mapping.'] as $dbfield => $formfield) {
			$mapping[$dbfield] = $formfield;
		}
		$this->mapping = $mapping;
	}

	/**
	 * Possibly unnecessary
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function initializeController($value = '') {
		//$this->piVars = t3lib_div::GParrayMerged($this->configuration->getPrefixedPackageKey());
	}

}
?>
