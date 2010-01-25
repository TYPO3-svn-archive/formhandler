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
 * A default view for Formhandler frontend listing
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	View
 */
class Tx_Formhandler_View_Listing extends Tx_Formhandler_AbstractView {

	/**
	 * Main method called by the controller.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $errors The errors occurred in validation
	 * @return string content
	 */
	public function render($gp, $errors) {

		$this->gp = $gp;

		//set template
		$this->template = $this->subparts['template'];

		//read settings
		$settings = $this->configuration->getSettings();
		if(!$settings['fe_listing.']) {
			throw new Exception('No config found!');
		}
		$settings = $settings['fe_listing.'];
		$this->settings = $settings;

		$subpart = $this->subparts['item'];
		$this->getMapping();
		$markers = array();
		if(!empty($this->model)) {
			foreach($this->model as $key => $row) {

				$markers = $this->getValueMarkers($row);

				$this->fillDefaultMarkers($markers, $row);
				$markerArray['###LIST###'] .= $this->cObj->substituteMarkerArray($subpart, $markers);
			}

			if(isset($this->gp['detailId'])) {

				$markerArray = $markers;
				$this->fillDefaultMarkers($markerArray);
			}

			$content = $this->cObj->substituteMarkerArray($this->template, $markerArray);
		}

		//remove markers that were not substituted
		$content = Tx_Formhandler_StaticFuncs::removeUnfilledMarkers($content);
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Fills the default markers:
	 * ###LINK2SHOW###
	 * ###DELETE###
	 * ###BACK_LINK###
	 *
	 * @param array &$markers reference to the marker array
	 * @param array &$row reference to the current record
	 * @return void
	 */
	protected function fillDefaultMarkers(&$markers, &$row = array()) {
		if($row['uid']) {
			$markers["###LINK2SHOW###"] = $this->cObj->getTypolink('Detail', $GLOBALS['TSFE']->id, array('formhandler' => array('detailId' => $row['uid'])));
			if($this->settings['enableDelete'] == 1) {
				$markers['###DELETE###'] = $this->cObj->getTypolink('X', $GLOBALS['TSFE']->id, array('formhandler' => array('deleteId' => $row['uid'])));
			}
		} elseif($this->settings['enableDelete'] == 1) {
			$markers['###DELETE###'] = $this->cObj->getTypolink('X', $GLOBALS['TSFE']->id, array('formhandler' => array('deleteId' => $this->gp['detailId'])));
		}
			

		$markers["###BACK_LINK###"] = $this->cObj->getTypolink('Back', $GLOBALS['TSFE']->id);
	}

	/**
	 * Function to parse the db field <-> marker name settings in TypoScript
	 *
	 * @return array The parsed mapping
	 */
	protected function getMapping() {
		if(!is_array($this->settings['mapping.'])) {
			return array();
		}

		$mapping = array();
		foreach($this->settings['mapping.'] as $dbfield => $formfield) {
			$mapping[$dbfield] = $formfield;
		}
		$this->mapping = $mapping;
	}

	/**
	 * Returns array with filled marker values of markers like ###value_[fieldname]###
	 *
	 * @param &$row The current record
	 * @return array The marker array
	 */
	protected function getValueMarkers(&$row) {
		$markers = array();

		foreach($row as $field => $value) {
				$mapping = $this->mapping[$field];
				$markers['###value_' . $mapping . '###'] = $value;
		}
		return $markers;
	}

}
?>
