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
 * Validates that a specified field's value matches the generated word of the extension "wt_calculating_captcha"
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_CalculatingCaptcha extends Tx_Formhandler_AbstractErrorCheck {

	public function check() {
		$checkFailed = '';
		if (t3lib_extMgm::isLoaded('wt_calculating_captcha')) {

				// include captcha class
			require_once(t3lib_extMgm::extPath('wt_calculating_captcha') . 'class.tx_wtcalculatingcaptcha.php');

				// generate object
			$captcha = t3lib_div::makeInstance('tx_wtcalculatingcaptcha');

				// check if code is correct
			if (!$captcha->correctCode($this->gp[$this->formFieldName])) {
				$checkFailed = $this->getCheckFailed();
			}
			unset($GLOBALS['TSFE']->fe_user->sesData['wt_calculating_captcha_value']);
		}

		return $checkFailed;
	}

}
?>