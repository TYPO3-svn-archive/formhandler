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
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_SetLanguage extends Tx_Formhandler_AbstractFinisher {

	public function process() {
		if(Tx_Formhandler_Globals::$session->get('originalLanguage') === NULL) {
			Tx_Formhandler_Globals::$session->set('originalLanguage', $GLOBALS['TSFE']->lang);
		}
		$languageCode = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'languageCode');
		if($languageCode) {
			$GLOBALS['TSFE']->lang = strtolower($languageCode);
			Tx_Formhandler_StaticFuncs::debugMessage('Language set to "' . $GLOBALS['TSFE']->lang . '"!', array(), 1);
		} else {
			Tx_Formhandler_StaticFuncs::debugMessage('Unable to set language! Language code set in TypoScript is empty!', array(), 2);
		}
		return $this->gp;
	}
	
	/**
	 * Method to define whether the config is valid or not. If no, display a warning on the frontend.
	 * The default value is TRUE. This up to the finisher to overload this method
	 *
	 */
	public function validateConfig() {
		$settings = Tx_Formhandler_Globals::$settings;
		if(is_array($settings['finishers.'])) {
			$found = FALSE;
			foreach($settings['finishers.'] as $finisherConfig) {
				if(strstr($finisherConfig['class'], 'Finisher_RestoreLanguage')) {
					$found = TRUE;
				}
			}
			if(!$found) {
				Tx_Formhandler_StaticFuncs::throwException('No Finisher_RestoreLanguage found in the TypoScript setup! You have to reset the language to the original value after you changed it using Finisher_SetLanguage');
			}
		}
		return $found;
	}

}
?>
