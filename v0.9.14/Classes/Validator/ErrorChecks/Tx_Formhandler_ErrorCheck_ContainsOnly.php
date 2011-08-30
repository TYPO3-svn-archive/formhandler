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
 * $Id: Tx_Formhandler_ErrorCheck_ContainsOne.php 30986 2010-03-10 18:34:49Z reinhardfuehricht $
 *                                                                        */

/**
 * Validates that a specified field contains only the specified words/characters
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_ContainsOnly extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field contains at least one of the specified words
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';
		$formValue = trim($gp[$name]);

		if (strlen($formValue) > 0) {
			$checkValue = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'words');
			if (!is_array($checkValue)) {
				$checkValue = t3lib_div::trimExplode(',', $checkValue);
			}
			$error = FALSE;
			$array = preg_split('//', $formValue, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($array as $idx => $char) {
				if (!in_array($char, $checkValue)) {
					$error = TRUE;
				}
			}
			if ($error) {

				//remove userfunc settings and only store comma seperated words
				$check['params']['words'] = implode(',', $checkValue);
				unset($check['params']['words.']);
				$checkFailed = $this->getCheckFailed($check);
			}
		}
		return $checkFailed;
	}

}
?>