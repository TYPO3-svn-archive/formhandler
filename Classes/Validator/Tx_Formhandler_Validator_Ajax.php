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

		$ts = Tx_Formhandler_Session::get('settings');
		$errors = array();
		$found = FALSE;

		$this->loadConfig($ts);
		if (is_array($this->settings['fieldConf.'])) {
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
					if (!isset($disableErrorCheckFields) || !in_array($name, $disableErrorCheckFields)) {
							
						//foreach error checks
						foreach ($errorChecks as $idx => $check) {
							$classNameFix = ucfirst($check['check']);
							$errorCheckObject = $this->componentManager->getComponent('Tx_Formhandler_ErrorCheck_' . $classNameFix);
							if ($name === $field && (empty($restrictErrorChecks) || in_array($check['check'], $restrictErrorChecks))) {
								$gp = array($field=>$value);
								$checkFailed = $errorCheckObject->check($check, $name, $gp);
								if (strlen($checkFailed) > 0) {
									if (!is_array($errors[$name])) {
										$errors[$name] = array();
									}
									array_push($errors[$name], $checkFailed);
								}
							}
						}
					}
				}
			}
		}

		if (empty($errors)) {
			$okImg = '<img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ok.png' . '" />';
			if ($ts['ajax.']['config.']['ok']) {
				$okImg = Tx_Formhandler_StaticFuncs::getSingle($ts['ajax.']['config.'], 'ok');
			}
			return $okImg;
		} else {
			$notOkImg = '<img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/notok.png' . '" />';
			if ($ts['ajax.']['config.']['notOk']) {
				$notOkImg = Tx_Formhandler_StaticFuncs::getSingle($ts['ajax.']['config.'], 'notOk');
			}
			return $notOkImg;
		}
	}

	public function loadConfig($tsConfig) {
		$this->settings = array();
		if ($tsConfig['validators.']) {
			foreach ($tsConfig['validators.'] as $idx => $settings) {
				if (is_array($settings['config.'])) {
					$this->settings = array_merge($this->settings, $settings['config.']);
				}
			}
		}
	}

}
?>