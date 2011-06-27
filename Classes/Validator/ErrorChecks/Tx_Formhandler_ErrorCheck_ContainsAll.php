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
 * Validates that a specified field contains all of the specified words
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_ContainsAll extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field contains all of the specified words
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
			$checkValue = $this->utilityFuncs->getSingle($check['params'], 'words');
			if (!is_array($checkValue)) {
				$checkValue = t3lib_div::trimExplode(',', $checkValue);
			}
			foreach ($checkValue as $idx => $word) {
				if (!stristr($formValue, $word)) {

						// remove userfunc settings and only store comma seperated words
					$check['params']['words'] = implode(',',$checkValue);
					unset($check['params']['words.']);
					$checkFailed = $this->getCheckFailed($check);
				}
			}
		}

		return $checkFailed;
	}

}
?>