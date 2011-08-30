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
 * Validates that a specified field's value is a valid time
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_Time extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field's value is a valid time
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';

		if (isset($gp[$name]) && strlen(trim($gp[$name])) > 0) {
			$pattern = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'pattern');
			preg_match('/^[h|m]*(.)[h|m]*/i', $pattern, $res);
			$sep = $res[1];
			$timeCheck = t3lib_div::trimExplode($sep, $gp[$name]);
			if (is_array($timeCheck)) {
				$hours = $timeCheck[0];
				if (!is_numeric($hours) || $hours < 0 || $hours > 23) {
					$checkFailed = $this->getCheckFailed($check);
				}
				$minutes = $timeCheck[1];
				if (!is_numeric($minutes) || $minutes < 0 || $minutes > 59) {
					$checkFailed = $this->getCheckFailed($check);
				}
			}
		}
		return $checkFailed;
	}

}
?>