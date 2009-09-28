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

		
		session_start();

		//set GET/POST parameters
		$this->gp = array();
		$this->gp = $gp;

		//set template
		$this->template = $this->subparts['template'];

		//set settings
		$this->settings = $this->parseSettings();

		//set language file
		if(!$this->langFiles) {
			$this->langFiles = Tx_Formhandler_Globals::$langFiles;
		}
		
		if($errors['mode'] != 'plain') {
			$this->sanitizeMarkers();
		}
		
		//substitute ISSET markers
		$this->substituteIssetSubparts();

		//fill TypoScript markers
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


		return trim($content);
	}
	
	/**
	 * Sanitizes GET/POST parameters by processing the 'checkBinaryCrLf' setting in TypoScript
	 *
	 * @return void
	 */
	protected function sanitizeMarkers() {
		$checkBinaryCrLf = $this->settings['checkBinaryCrLf'];
		if ($checkBinaryCrLf != '') {
			$paramsToCheck = t3lib_div::trimExplode(',', $checkBinaryCrLf);
			foreach($paramsToCheck as &$val) {
				if(!is_array($val)) {
					$val = str_replace (chr(13), '<br />', $val);
					$val = str_replace ('\\', '', $val);
					$val = nl2br($val);
				}
			}
		}
	}

}
?>