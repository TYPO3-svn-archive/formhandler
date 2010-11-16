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
 * Validates that a specified field's value is a valid date
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_Date extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field's value is a valid date
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';

		if (isset($gp[$name]) && strlen(trim($gp[$name])) > 0) {
			# find out separator
			$pattern = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'pattern');
			preg_match('/^[d|m|y]*(.)[d|m|y]*/i', $pattern, $res);
			$sep = $res[1];

			// normalisation of format
			$pattern = $this->normalizeDatePattern($pattern, $sep);

			// find out correct positioins of "d","m","y"
			$pos1 = strpos($pattern, 'd');
			$pos2 = strpos($pattern, 'm');
			$pos3 = strpos($pattern, 'y');
			$dateCheck = t3lib_div::trimExplode($sep, $gp[$name]);
			if (sizeof($dateCheck) !== 3) {
				$checkFailed = $this->getCheckFailed($check);
			} elseif (intval($dateCheck[0]) === 0 || intval($dateCheck[1]) === 0 || intval($dateCheck[2]) === 0) {
				$checkFailed = $this->getCheckFailed($check);
			} elseif (!checkdate($dateCheck[$pos2], $dateCheck[$pos1], $dateCheck[$pos3])) {
				$checkFailed = $this->getCheckFailed($check);
			} elseif (strlen($dateCheck[$pos3]) !== 4) {
				$checkFailed = $this->getCheckFailed($check);
			}
		}
		return $checkFailed;
	}

	/**
	 * Internal method to normalize a specified date pattern for internal use
	 *
	 * @param string $pattern The pattern
	 * @param string $sep The separator character
	 * @return string The normalized pattern
	 */
	protected function normalizeDatePattern($pattern,$sep) {
		$pattern = strtoupper($pattern);
		$pattern = str_replace($sep, '', $pattern);
		$pattern = str_replace('DD', 'd', $pattern);
		$pattern = str_replace('D', 'd', $pattern);
		$pattern = str_replace('MM', 'm', $pattern);
		$pattern = str_replace('M', 'm', $pattern);
		$pattern = str_replace('YYYY', 'y', $pattern);
		$pattern = str_replace('YY', 'y', $pattern);
		$pattern = str_replace('Y', 'y', $pattern);
		return $pattern;
	}
}
?>