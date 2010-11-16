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
 * A default validator for Formhandler providing basic validations.
 *
 * Example configuration:
 *
 * <code>
 * plugin.Tx_Formhandler.settings.validators.1.class = Tx_Formhandler_Validator_Default
 *
 * # single error check
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.firstname.errorCheck.1 = required
 *
 * #multiple error checks for one field
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.email.errorCheck.1 = required
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.email.errorCheck.2 = email
 *
 * #error checks with parameters
 * #since the parameter for the error check "minLength" is "value", you can use a marker ###value### in your error message.
 * #E.g. The lastname has to be at least ###value### characters long.
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.lastname.errorCheck.1 = required
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.lastname.errorCheck.2 = minLength
 * plugin.Tx_Formhandler.settings.validators.1.config.fieldConf.lastname.errorCheck.2.value = 2
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Validator
 */
class Tx_Formhandler_Validator_Default extends Tx_Formhandler_AbstractValidator {

	/**
	 * Method to set GET/POST for this class and load the configuration
	 *
	 * @param array The GET/POST values
	 * @param array The TypoScript configuration
	 * @return void
	 */
	public function init($gp, $tsConfig) {
		$this->settings = $tsConfig;

		$flexformValue = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], 'required_fields', 'sMISC');
		if ($flexformValue) {
			$fields = t3lib_div::trimExplode(',', $flexformValue);
			foreach ($fields as $idx => $field) {
				if (!is_array($this->settings['fieldConf.'][$field.'.']['errorCheck.'])) {
					$this->settings['fieldConf.'][$field.'.']['errorCheck.'] = array();
				}
				if (!array_search('required', $this->settings['fieldConf.'][$field.'.']['errorCheck.'])) {
					array_push($this->settings['fieldConf.'][$field.'.']['errorCheck.'], 'required');
				}
			}
		}

		$this->gp = $gp;
	}

	/**
	 * Validates the submitted values using given settings
	 *
	 * @param array &$errors Reference to the errors array to store the errors occurred
	 * @return boolean
	 */
	public function validate(&$errors) {

		//no config? validation returns TRUE
		if (!is_array($this->settings['fieldConf.'])) {
			return TRUE;
		}

		$disableErrorCheckFields = array();
		if (isset($this->settings['disableErrorCheckFields'])) {
			$disableErrorCheckFields = t3lib_div::trimExplode(',', $this->settings['disableErrorCheckFields']);
		}

		$restrictErrorChecks = array();
		if (isset($this->settings['restrictErrorChecks'])) {
			$restrictErrorChecks = t3lib_div::trimExplode(',', $this->settings['restrictErrorChecks']);
		}

		//foreach configured form field
		foreach ($this->settings['fieldConf.'] as $fieldName => $fieldSettings) {
			$name = str_replace('.', '', $fieldName);

			//parse error checks
			if (is_array($fieldSettings['errorCheck.'])) {
				$counter = 0;
				$errorChecks = array();

				//set required to first position if set
				foreach ($fieldSettings['errorCheck.'] as $key => $check) {
					if (!strstr($key, '.')) {
						if (!strcmp($check, 'required') || !strcmp($check, 'file_required')) {
							$errorChecks[$counter]['check'] = $check;
							unset($fieldSettings['errorCheck.'][$key]);
							$counter++;
						}
					}
				}

				//set other errorChecks
				foreach ($fieldSettings['errorCheck.'] as $key=>$check) {
					if (!strstr($key, '.')) {
						$errorChecks[$counter]['check'] = $check;
						if (is_array($fieldSettings['errorCheck.'][$key . '.'])) {
							$errorChecks[$counter]['params'] = $fieldSettings['errorCheck.'][$key . '.'];
						}
						$counter++;
					}
				}

				$checkFailed = '';
				if (!isset($disableErrorCheckFields) || (!in_array($name, $disableErrorCheckFields) && !in_array('all', $disableErrorCheckFields))) {

					//foreach error checks
					foreach ($errorChecks as $idx => $check) {
						$classNameFix = ucfirst($check['check']);
						$errorCheckObject = $this->componentManager->getComponent('Tx_Formhandler_ErrorCheck_' . $classNameFix);
						if (!$errorCheckObject) {
							Tx_Formhandler_StaticFuncs::debugMessage('check_not_found', 'Tx_Formhandler_ErrorCheck_' . $classNameFix);
						}
						if (empty($restrictErrorChecks) || in_array($check['check'], $restrictErrorChecks)) {
							Tx_Formhandler_StaticFuncs::debugMessage('calling_class', 'Tx_Formhandler_ErrorCheck_' . $classNameFix);
							$checkFailed = $errorCheckObject->check($check, $name, $this->gp);
							if (strlen($checkFailed) > 0) {
								if (!is_array($errors[$name])) {
									$errors[$name] = array();
								}
								array_push($errors[$name], $checkFailed);
							}
						} else {
							Tx_Formhandler_StaticFuncs::debugMessage('check_skipped', $check['check']);
						}
					}
				}
			}
		}
		return empty($errors);
	}

}
?>