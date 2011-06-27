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
 * Validates that a specified field is an integer and greater than or equal a specified value
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_MinValue extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field is an integer and greater than or equal a specified value
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';
		$min = floatval(str_replace(',', '.', $this->utilityFuncs->getSingle($check['params'], 'value')));
		$valueToCheck = floatval(str_replace(',', '.', $gp[$name]));
		if (isset($gp[$name]) &&
			$valueToCheck >= 0 &&
			$min >= 0 &&
			(!is_numeric($valueToCheck) || $valueToCheck < $min)) {

			$checkFailed = $this->getCheckFailed($check);
		}
		return $checkFailed;
	}

}
?>