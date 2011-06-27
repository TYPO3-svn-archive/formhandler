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
class Tx_Formhandler_Finisher_RestoreLanguage extends Tx_Formhandler_AbstractFinisher {

	public function process() {
		if($this->globals->getSession()->get('originalLanguage') !== NULL) {
			$GLOBALS['TSFE']->lang = $this->globals->getSession()->get('originalLanguage');
			$this->globals->getSession()->set('originalLanguage', NULL);
			$this->utilityFuncs->debugMessage('Language restored to "' . $GLOBALS['TSFE']->lang . '"!', array(), 1);
		} else {
			$this->utilityFuncs->debugMessage('Unable to restore language! No original language found!', array(), 2);
		}

		return $this->gp;
	}

}
?>
