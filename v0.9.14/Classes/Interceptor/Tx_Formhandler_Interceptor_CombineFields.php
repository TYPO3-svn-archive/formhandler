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
 * $Id: Tx_Formhandler_Interceptor_Default.php 27708 2009-12-15 09:22:07Z reinhardfuehricht $
 *                                                                        */

/**
 * Combines values entered in form field and stores it in a new entry in $this->gp.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Interceptor
 */
class Tx_Formhandler_Interceptor_CombineFields extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		if (is_array($this->settings['combineFields.'])) {
			foreach ($this->settings['combineFields.'] as $newField => $options) {
				$newField = str_replace('.', '', $newField);
				if (is_array($options['fields.'])) {
					$this->gp[$newField] = $this->combineFields($options);
					Tx_Formhandler_StaticFuncs::debugMessage('combined', array($newField, $this->gp[$newField]));
				}
			}
		}
		return $this->gp;
	}

	protected function combineFields($options) {
		if (!isset($options['separator'])) {
			$separator = ' ';
		} else {
			$separator = $options['separator'];
		}
		$fieldsArr = $options['fields.'];
		$combinedString = '';
		foreach ($fieldsArr as $idx => $field) {
			if (intval($options['hideEmptyValues']) === 0 || 
				(intval($options['hideEmptyValues']) === 1 && isset($this->gp[$field]) && strlen($this->gp[$field]) > 0)) {
				$combinedString .= $this->gp[$field] . $separator;
			}
		}
		if ($combinedString) {
			$combinedString = substr($combinedString, 0, (strlen($combinedString) - 1));
		}
		return $combinedString; 
	}

}
?>
