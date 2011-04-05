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
 * Abstract class for validators for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Validator
 */
class Tx_Formhandler_ErrorCheck_FileMinCount extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that at least x files get uploaded via the specified upload field.
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';

		$files = Tx_Formhandler_Globals::$session->get('files');
		$settings = Tx_Formhandler_Globals::$session->get('settings');
		$currentStep = Tx_Formhandler_Globals::$session->get('currentStep');
		$lastStep = Tx_Formhandler_Globals::$session->get('lastStep');
		$minCount = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'minCount');
		if (is_array($files[$name]) &&
			$currentStep > $lastStep) {

			foreach ($_FILES as $idx => $info) {
				if (strlen($info['name'][$name]) > 0 && count($files[$name]) < ($minCount - 1)) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif (strlen($info['name'][$name]) === 0 && count($files[$name]) < $minCount) {
					$checkFailed = $this->getCheckFailed($check);
				}
			}
		}

		return $checkFailed;
	}

}
?>