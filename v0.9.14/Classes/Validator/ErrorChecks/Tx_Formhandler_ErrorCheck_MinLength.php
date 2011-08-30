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
 * Validates that a specified field is a string and at least a specified count of characters long
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_MinLength extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field is a string and at least a specified count of characters long
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';
		$min = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'value');
		if (isset($gp[$name]) &&
			mb_strlen(trim($gp[$name]), $GLOBALS['TSFE']->renderCharset) > 0 &&
			intVal($min) > 0 &&
			mb_strlen(trim($gp[$name]), $GLOBALS['TSFE']->renderCharset) < intval($min)) {

			$checkFailed = $this->getCheckFailed($check);
		}
		return $checkFailed;
	}

}
?>