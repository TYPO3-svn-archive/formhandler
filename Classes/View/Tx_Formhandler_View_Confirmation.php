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
 * A view for Finisher_Confirmation used by Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	View
 */
class Tx_Formhandler_View_Confirmation extends Tx_Formhandler_View_Form {

	/**
	 * Main method called by the controller.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $errors The errors occurred in validation
	 * @return string content
	 */
	public function render($gp, $errors) {

			//set GET/POST parameters
		$this->gp = $gp;

			//set template
		$this->template = $this->subparts['template'];

			//set settings
		$this->settings = $this->parseSettings();

			//set language file
		if(!$this->langFile) {
			$this->readLangFile();
		}

			//substitute ISSET markers
		$this->substituteIssetSubparts();

			//fill Typoscript markers
		if(is_array($this->settings['markers.'])) {
			$this->fillTypoScriptMarkers();
		}

			//fill default markers
		$this->fillDefaultMarkers();

			//fill value_[fieldname] markers
		$this->fillValueMarkers();

			//fill LLL:[language_key] markers
		$this->fillLangMarkers();

			//remove markers that were not substituted
		$content = Tx_Formhandler_StaticFuncs::removeUnfilledMarkers($this->template);

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * This function fills the default markers:
	 *
	 * ###PRINT_LINK###
	 * ###PDF_LINK###
	 * ###CSV_LINK###
	 *
	 * @return string Template with replaced markers
	 */
	protected function fillDefaultMarkers() {
		parent::fillDefaultMarkers();
		if($this->settings['formValuesPrefix']) {
			$params[$this->settings['formValuesPrefix']] = $this->gp;
		} else {
			$params = $this->gp;
		}
		$params['type'] = 98;
		$label = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':print'));
		if(strlen($label) == 0) {
			$label = 'print';
		}
		$markers['###PRINT_LINK###'] = $this->cObj->getTypolink($label, $GLOBALS['TSFE']->id, $params);
		unset($params['type']);
		if($this->settings['formValuesPrefix']) {
			$params[$this->settings['formValuesPrefix']]['renderMethod'] = 'pdf';
		} else {
			$params['renderMethod'] = 'pdf';
		}
		
		$label = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':pdf'));
		if(strlen($label) == 0) {
			$label = 'pdf';
		}
		$markers['###PDF_LINK###'] = $this->cObj->getTypolink($label, $GLOBALS['TSFE']->id, $params);
		if($this->settings['formValuesPrefix']) {
			$params[$this->settings['formValuesPrefix']]['renderMethod'] = 'csv';
		} else {
			$params['renderMethod'] = 'csv';
		}
		
		$label = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':csv'));
		if(strlen($label) == 0) {
			$label = 'csv';
		}
		$markers['###CSV_LINK###'] = $this->cObj->getTypolink($label, $GLOBALS['TSFE']->id, $params);
		
		$this->fillFEUserMarkers($markers);
		$this->fillFileMarkers($markers);
		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}
}
?>
