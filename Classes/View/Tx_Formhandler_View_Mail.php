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
 * A default view for Formhandler E-Mails
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	View
 */
class Tx_Formhandler_View_Mail extends Tx_Formhandler_View_Form {

	/**
	 * Main method called by the controller.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $errors In this class the second param is used to pass information about the email mode (HTML|PLAIN)
	 * @return string content
	 */
	public function render($gp, $errors) {

		//set GET/POST parameters
		$this->gp = array();
		$this->gp = $gp;

		//set template
		$this->template = $this->subparts['template'];

		//set settings
		$this->settings = $this->parseSettings();

		//set language file
		if (!$this->langFiles) {
			$this->langFiles = $this->globals->getLangFiles();
		}

		$componentSettings = $this->getComponentSettings();
		if ($componentSettings[$errors['mode']][$errors['suffix'] . '.']['arrayValueSeparator']) {
			$this->settings['arrayValueSeparator'] = $componentSettings[$errors['mode']][$errors['suffix'] . '.']['arrayValueSeparator'];
			$this->settings['arrayValueSeparator.'] = $componentSettings[$errors['mode']][$errors['suffix'] . '.']['arrayValueSeparator.'];
		}
		if ($errors['suffix'] != 'plain') {
			$this->sanitizeMarkers();
		}

		//read master template
		if (!$this->masterTemplates) {
			$this->readMasterTemplates();
		}

		if (!empty($this->masterTemplates)) {
			$this->replaceMarkersFromMaster();
		}

		//substitute ISSET markers
		$this->substituteIssetSubparts();

		//fill TypoScript markers
		if (is_array($this->settings['markers.'])) {
			$this->fillTypoScriptMarkers();
		}

		//fill default markers
		$this->fillDefaultMarkers();
		
		if(intval($this->settings['fillValueMarkersBeforeLangMarkers']) === 1) {
			
			//fill value_[fieldname] markers
			$this->fillValueMarkers();
		}

		//fill LLL:[language_key] markers
		$this->fillLangMarkers();
		
		$this->fillSelectedMarkers();

		if(intval($this->settings['fillValueMarkersBeforeLangMarkers']) !== 1) {
			
			//fill value_[fieldname] markers
			$this->fillValueMarkers();
		}

		//remove markers that were not substituted
		$content = $this->utilityFuncs->removeUnfilledMarkers($this->template);
		return trim($content);
	}

	/**
	 * Sanitizes GET/POST parameters by processing the 'checkBinaryCrLf' setting in TypoScript
	 *
	 * @return void
	 */
	protected function sanitizeMarkers() {
		$componentSettings = $this->getComponentSettings();
		$checkBinaryCrLf = $componentSettings['checkBinaryCrLf'];
		if ($checkBinaryCrLf != '') {
			$paramsToCheck = t3lib_div::trimExplode(',', $checkBinaryCrLf);
			foreach ($paramsToCheck as $idx => $field) {
				if (!is_array($field)) {
					$this->gp[$field] = str_replace (chr(13), '', $this->gp[$field]);
					$this->gp[$field] = str_replace ('\\', '', $this->gp[$field]);
					$this->gp[$field] = nl2br($this->gp[$field]);
				}
			}
		}
	}

}
?>