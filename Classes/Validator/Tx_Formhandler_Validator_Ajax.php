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
 * $Id: Tx_Formhandler_Validator_Default.php 23307 2009-08-12 14:34:30Z reinhardfuehricht $
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
class Tx_Formhandler_Validator_Ajax extends Tx_Formhandler_AbstractValidator {

	public function validate(&$errors) {
		//Nothing to do here
		return TRUE;
	}
	
	
	/**
	 * Validates the submitted values using given settings
	 *
	 * @param array &$errors Reference to the errors array to store the errors occurred
	 * @return boolean
	 */
	public function validateAjax($field, $value) {
		
		if(!t3lib_extMgm::isLoaded('xajax')) {
			return;
		}

		if(!class_exists('tx_xajax_response')) {
			// Instantiate the tx_xajax_response object
			require (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax_response.php');
		}
		
		session_start();
		
		$this->loadConfig(array(), $_SESSION['formhandlerSettings']['settings']);
		
		
		$objResponse = new tx_xajax_response();
		
		//no config? validation returns true
		if(!is_array($this->settings['fieldConf.'])) {
			$objResponse->assign('Error_' . $field, 'innerHTML', 'No settings');
			
			//return the XML response
			return $objResponse;
		}

		$disableErrorCheckFields = array();
		if(isset($this->settings['disableErrorCheckFields'])) {
			$disableErrorCheckFields = t3lib_div::trimExplode(',', $this->settings['disableErrorCheckFields']);
		}
		
		$restrictErrorChecks = array();
		if(isset($this->settings['restrictErrorChecks'])) {
			$restrictErrorChecks = t3lib_div::trimExplode(',', $this->settings['restrictErrorChecks']);
		}


		//foreach configured form field
		foreach($this->settings['fieldConf.'] as $fieldName => $fieldSettings) {
			$name = str_replace('.', '', $fieldName);
				
			//parse error checks
			if(is_array($fieldSettings['errorCheck.'])) {
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
				if(!isset($disableErrorCheckFields) || !in_array($name, $disableErrorCheckFields)) {
						
					//foreach error checks
					foreach($errorChecks as $check) {
						$classNameFix = ucfirst($check['check']);
						$errorCheckObject = $this->componentManager->getComponent('Tx_Formhandler_ErrorCheck_' . $classNameFix);
						if(!$errorCheckObject) {
							Tx_Formhandler_StaticFuncs::debugMessage('check_not_found', 'Tx_Formhandler_ErrorCheck_' . $classNameFix);
						}
						
						if($name === $field && (empty($restrictErrorChecks) || in_array($check['check'], $restrictErrorChecks))) {
							Tx_Formhandler_StaticFuncs::debugMessage('calling_class', 'Tx_Formhandler_ErrorCheck_' . $classNameFix);
							$gp = array($field=>$value);
							$checkFailed = $errorCheckObject->check($check, $name, $gp);
							if(strlen($checkFailed) > 0) {
								if(!is_array($errors[$name])) {
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
		if(empty($errors)) {
			$objResponse->assign('loading_' . $field, 'style.display', 'none');
			$message = '<img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ok.png' . '" />';
			$objResponse->assign('error_' . $field, 'innerHTML', $message);
			$objResponse->assign('error_' . $field, 'style.display', 'inline');
		} else {
			$types = $errors[$field];
			
			$message = Tx_Formhandler_StaticFuncs::getErrorMessage($field, $types);
			$objResponse->assign('loading_' . $field, 'style.display', 'none');
			$objResponse->assign('error_' . $field, 'innerHTML', $message);
			$objResponse->assign('error_' . $field, 'style.display', 'inline');
			
		}
		
		//return the XML response
		return $objResponse;

	}
	
	public function loadConfig($gp, $tsConfig) {
		$this->settings = array();
		if($tsConfig['validators.']) {
			foreach($tsConfig['validators.'] as $settings) {
				if(is_array($settings['config.'])) {
					
					$this->settings = array_merge($this->settings, $settings['config.']);
				}
			}
		}
		
	}

}
?>