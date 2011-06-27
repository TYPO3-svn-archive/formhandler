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
 * Validates that a specified field's value is a valid date and between two specified dates
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_DateRange extends Tx_Formhandler_ErrorCheck_Date {

	/**
	 * Validates that a specified field's value is a valid date and between two specified dates
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';

		if (isset($gp[$name]) && strlen(trim($gp[$name])) > 0) {
			$min = $this->utilityFuncs->getSingle($check['params'], 'min');
			$max = $this->utilityFuncs->getSingle($check['params'], 'max');
			$pattern = $this->utilityFuncs->getSingle($check['params'], 'pattern');
			preg_match('/^[d|m|y]*(.)[d|m|y]*/i', $pattern, $res);
			$sep = $res[1];

			// normalisation of format
			$pattern = $this->normalizeDatePattern($pattern,$sep);

			// find out correct positioins of "d","m","y"
			$pos1 = strpos($pattern, 'd');
			$pos2 = strpos($pattern, 'm');
			$pos3 = strpos($pattern, 'y');
			$date = $gp[$name];
			$checkdate = explode($sep,$date);
			$check_day = $checkdate[$pos1];
			$check_month = $checkdate[$pos2];
			$check_year = $checkdate[$pos3];
			if (strlen($min) > 0) {
				$min_date = t3lib_div::trimExplode($sep, $min);
				$min_day = $min_date[$pos1];
				$min_month = $min_date[$pos2];
				$min_year = $min_date[$pos3];
				if ($check_year < $min_year) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif ($check_year == $min_year && $check_month < $min_month) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif ($check_year == $min_year && $check_month == $min_month && $check_day < $min_day) {
					$checkFailed = $this->getCheckFailed($check);
				}
			}
			if (strlen($max) > 0) {
				$max_date = t3lib_div::trimExplode($sep, $max);
				$max_day = $max_date[$pos1];
				$max_month = $max_date[$pos2];
				$max_year = $max_date[$pos3];
				if ($check_year > $max_year) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif ($check_year == $max_year && $check_month > $max_month) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif ($check_year == $max_year && $check_month == $max_month && $check_day > $max_day) {
					$checkFailed = $this->getCheckFailed($check);
				}
			}
		}

		return $checkFailed;
	}
}
?>