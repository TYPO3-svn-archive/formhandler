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
 * Abstract class for error checks for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
abstract class Tx_Formhandler_AbstractErrorCheck extends Tx_Formhandler_AbstractComponent {

	public function process() {
		return;
	}

	/**
	 * Performs the specific error check.
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	abstract public function check(&$check, $name, &$gp);

	/**
	 * Sets the suitable string for the checkFailed message parsed in view.
	 *
	 * @param array $check The parsed check settings
	 * @return string The check failed string
	 */
	protected function getCheckFailed($check) {
		$checkFailed = $check['check'];
		if (is_array($check['params'])) {
			$checkFailed .= ';';
			foreach ($check['params'] as $key => $value) {
				$checkFailed .= $key . '::' . $value . ';';
			}
			$checkFailed = substr($checkFailed, 0, (strlen($checkFailed) - 1));
		}
		return $checkFailed;
	}

}
?>
