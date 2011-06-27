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
 * $Id: Tx_Formhandler_PreProcessor_LoadGetPost.php 22614 2009-07-21 20:43:47Z fabien_u $
 *                                                                        */

/**
 * A pre processor for Formhandler loading GET/POST parameters passed from another page.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	PreProcessor
 */
class Tx_Formhandler_PreProcessor_LoadGetPost extends Tx_Formhandler_AbstractPreProcessor {

	/**
	 * Main method called by the controller.
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$this->formValuesPrefix = $this->globals->getFormValuesPrefix();
		$loadedGP = $this->loadGP();
		$this->gp = array_merge($loadedGP, $this->gp);
		return $this->gp;
	}

	protected function loadGP() {
		$gp = array_merge(t3lib_div::_GET(), t3lib_div::_POST());
		if ($this->formValuesPrefix) {
			$gp = $gp[$this->formValuesPrefix];
		}
		if (!is_array($gp)) {
			$gp = array();
		}
		return $gp;
	}

}
?>
