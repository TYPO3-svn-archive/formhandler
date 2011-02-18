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
 * Validates that a specified field's value is found in a specified db table
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_IsInDBTable extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that a specified field's value is found in a specified db table
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';
		
		if (isset($gp[$name]) && strlen(trim($gp[$name])) > 0) {
			$checkTable = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'table');
			$checkField = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'field');
			$additionalWhere = Tx_Formhandler_StaticFuncs::getSingle($check['params'], 'additionalWhere');
			if (!empty($checkTable) && !empty($checkField)) {
				$where = $checkField . '=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($gp[$name], $checkTable) . ' ' . $additionalWhere;
				$showHidden = intval($check['params']['showHidden']) === 1 ? 1 : 0;
				$where .= $GLOBALS['TSFE']->sys_page->enableFields($checkTable, $showHidden);
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($checkField, $checkTable, $where);
				if ($res && !$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
					$checkFailed = $this->getCheckFailed($check);
				} elseif (!$res) {
					Tx_Formhandler_StaticFuncs::debugMessage('error', array($GLOBALS['TYPO3_DB']->sql_error()), 3);
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
			}
		}
		return $checkFailed;
	}

}
?>