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
 * @author Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	Validator
 */
class Tx_Formhandler_Validator_Default extends Tx_Formhandler_AbstractValidator {

	protected $restrictErrorChecks = array();
	
	protected $disableErrorCheckFields = array();

	/**
	 * Method to set GET/POST for this class and load the configuration
	 *
	 * @param array The GET/POST values
	 * @param array The TypoScript configuration
	 * @return void
	 */
	public function init($gp, $tsConfig) {
		$this->settings = $tsConfig;

		$flexformValue = $this->utilityFuncs->pi_getFFvalue($this->cObj->data['pi_flexform'], 'required_fields', 'sMISC');
		if($flexformValue) {
			$fields = t3lib_div::trimExplode(',', $flexformValue);
			foreach($fields as $field) {
				if(!is_array($this->settings['fieldConf.'][$field.'.']['errorCheck.'])) {
					$this->settings['fieldConf.'][$field.'.']['errorCheck.'] = array();
				}
				if(!array_search('required', $this->settings['fieldConf.'][$field.'.']['errorCheck.'])) {
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
		if(!is_array($this->settings['fieldConf.'])) {
			return TRUE;
		}

		if(isset($this->settings['disableErrorCheckFields'])) {
			$this->disableErrorCheckFields = t3lib_div::trimExplode(',', $this->settings['disableErrorCheckFields']);
		}
		
		if(isset($this->settings['restrictErrorChecks'])) {
			$this->restrictErrorChecks = t3lib_div::trimExplode(',', $this->settings['restrictErrorChecks']);
		}
		
		if (!in_array('all', $this->disableErrorCheckFields)) {
			$errors = $this->validateRecursive($errors, $this->gp, (array) $this->settings['fieldConf.']);
		}
		
		if ($this->settings['messageLimit'] > 0 || is_array($this->settings['messageLimit.'])) {
			$limit = (int) $this->settings['messageLimit'];
			$limits = (array) $this->settings['messageLimit.'];
			
			foreach ($errors as $field => $messages) {
				if (isset($limits[$field]) && $limits[$field] > 0) {
					$errors[$field] = array_slice($messages, - $limits[$field]);
				}elseif ($limit > 0) {
					$errors[$field] = array_slice($messages, - $limit);
				}
			}
		}
		
		return empty($errors);
	}
	
	/**
	 * Recursively calls the configured errorChecks. It's possible to setup
	 * errorChecks for each key in multidimensional arrays:
	 * 
	 * <code title="errorChecks for arrays">
	 * <input type="text" name="birthdate[day]"/>
	 * <input type="text" name="birthdate[month]"/>
	 * <input type="text" name="birthdate[year]"/>
	 * <input type="text" name="name"/>
	 * 
	 * validators.1.config.fieldConf {
	 *   birthdate {
	 *     day.errorCheck {
	 *       1 = betweenValue
	 *       1.minValue = 1
	 *       1.maxValue = 31
	 *     }
	 *     month.errorCheck {
	 *       1 = betweenValue
	 *       1.minValue = 1
	 *       1.maxValue = 12
	 *     }
	 *     year.errorCheck {
	 *       1 = minValue
	 *       1.minValue = 45
	 *     }
	 *   }
	 *   birthdate.errorCheck.1 = maxItems
	 *   birthdate.errorCheck.1.value = 3
	 *   name.errorCheck.1 = required
	 * }
	 * </code>
	 * 
	 * @param array $errors
	 * @param array $gp
	 * @param array $fieldConf
	 * @param string $rootField
	 * @return array The error array
	 */
	protected function validateRecursive($errors, $gp, $fieldConf, $rootField = null) {
		//foreach configured form field
		foreach($fieldConf as $key => $fieldSettings) {
			
			$fieldName = trim($key, '.');
			$errorFieldName = ($rootField === null) ? $fieldName : $rootField;

		
			if(in_array($errorFieldName, $this->disableErrorCheckFields)) {
				continue;
			}
			
			$tempSettings = $fieldSettings;
			unset($tempSettings['errorCheck.']);
			if (count($tempSettings)) {
				// Nested field-confs - do recursion:
				$errors = $this->validateRecursive($errors, (array) $gp[$fieldName], $tempSettings, $errorFieldName);
			}
			
			if (!is_array($fieldSettings['errorCheck.'])) {
				continue;
			}
			
			$counter = 0;
			$errorChecks = array();
			
			//set required to first position if set
			foreach($fieldSettings['errorCheck.'] as $key => $check) {
				if(!strstr($key, '.')) {
					if(!strcmp($check, 'required') || !strcmp($check, 'file_required')) {
						$errorChecks[$counter]['check'] = $check;
						unset($fieldSettings['errorCheck.'][$key]);
						$counter++;
					}
				}
			}

			//set other errorChecks
			foreach($fieldSettings['errorCheck.'] as $key=>$check) {
				if(!strstr($key, '.')) {
					$errorChecks[$counter]['check'] = $check;
					if(is_array($fieldSettings['errorCheck.'][$key . '.'])) {
						$errorChecks[$counter]['params'] = $fieldSettings['errorCheck.'][$key . '.'];
					}
					$counter++;
				}
			}

			
			$checkFailed = '';		
				//foreach error checks
			foreach($errorChecks as $check) {
				$classNameFix = ucfirst($check['check']);
				$errorCheckObject = $this->componentManager->getComponent('Tx_Formhandler_ErrorCheck_' . $classNameFix);
				if(!$errorCheckObject) {
					$this->utilityFuncs->debugMessage('check_not_found', array('Tx_Formhandler_ErrorCheck_' . $classNameFix), 2);
				}
				if(empty($this->restrictErrorChecks) || in_array($check['check'], $this->restrictErrorChecks)) {
					$this->utilityFuncs->debugMessage('calling_class', array('Tx_Formhandler_ErrorCheck_' . $classNameFix));
					$checkFailed = $errorCheckObject->check($check, $fieldName, $gp);
					if(strlen($checkFailed) > 0) {
						if(!is_array($errors[$errorFieldName])) {
							$errors[$errorFieldName] = array();
						}
						$errors[$errorFieldName][] = $checkFailed;
					}
				} else {
					$this->utilityFuncs->debugMessage('check_skipped', array($check['check']));
				}
			}
		}
		return $errors;
	}

}
?>