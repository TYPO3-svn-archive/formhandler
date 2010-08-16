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
 * This PreProcessor adds the posibility to load default values.
 * Values fot the first step are loaded to $gp values of other steps are stored
 * to the session.
 *
 * Example configuration:
 *
 * <code>
 * preProcessors.1.class = Tx_Formhandler_PreProcessor_LoadDefaultValues
 * preProcessors.1.config.1.contact_via.defaultValue = email
 * preProcessors.1.config.2.[field1].defaultValue = 0
 * preProcessors.1.config.2.[field2].defaultValue {
 *       data = date : U
 *       strftime = %A, %e. %B %Y
 * }
 * preProcessors.1.config.2.[field3].defaultValue < plugin.tx_exampleplugin
 * <code>
 *
 * may copy the TS to the default validator settings to avoid redundancy
 * Example:
 *
 * plugin.Tx_Formhandler.settings.predef.multistep_example.2.validators.1.config.fieldConf.[field].errorcheck.1.notDefaultValue
 * plugin.Tx_Formhandler.settings.predef.multistep_example.2.validators.1.config.fieldConf.[field].errorcheck.1.notDefaultValue.defaultValue < plugin.Tx_Formhandler.settings.predef.multistep_example.preProcessors.1.config.1.[field].defaultValue
 *
 * @author	Johannes Feustel
 * @package	Tx_Formhandler
 * @subpackage	PreProcessor
 */

class Tx_Formhandler_PreProcessor_LoadDefaultValues extends Tx_Formhandler_AbstractPreProcessor {

	public function process() {
		

		foreach ($this->settings as $step => $stepSettings){
			$step= preg_replace('/\.$/', '', $step);

			if ($step == 1){
				$this->loadDefaultValuesToGP($stepSettings);
			} elseif(is_numeric($step)) {
				$this->loadDefaultValuesToSession($stepSettings, $step);
			}
		}

		return $this->gp;
	}

	/**
	 * adapted from class tx_thmailformplus_pi1
	 * Loads the default values to the GP Array
	 *
	 * @return void
	 * @param array $settings
	 */
	function loadDefaultValuesToGP($settings) {

		if (is_array($settings)) {
			foreach (array_keys($settings) as $fN) {
				$fN = preg_replace('/\.$/', '', $fN);
				
				if (!isset($this->gp[$fN])) {
					$this->gp[$fN] = Tx_Formhandler_StaticFuncs::getSingle($settings[$fN . '.'], 'defaultValue');
					if($settings[$fN . '.']['defaultValue.']['separator']) {
						$separator = $settings[$fN . '.']['defaultValue.']['separator'];
						$this->gp[$fN] = t3lib_div::trimExplode($separator, $this->gp[$fN]);
					}
				}
			}
		}
	}

	/**
	 * loads the Default Setting in the Session. Used only for step 2+.
	 *
	 * @return void
	 * @param Array $settings
	 * @param int $step
	 */
	private function loadDefaultValuesToSession($settings, $step){

		if (is_array($settings) && $step) {
			$values = Tx_Formhandler_Session::get('values');
			foreach (array_keys($settings) as $fN) {
				$fN = preg_replace('/\.$/', '', $fN);
				
				if (!isset($values[$step][$fN])) {
					$values[$step][$fN] = Tx_Formhandler_StaticFuncs::getSingle($settings[$fN . '.'], 'defaultValue');
					if($settings[$fN . '.']['defaultValue.']['separator']) {
						$separator = $settings[$fN . '.']['defaultValue.']['separator'];
						$values[$step][$fN] = t3lib_div::trimExplode($separator, $this->gp[$fN]);
					}
				}

			}
			Tx_Formhandler_Session::set('values', $values);
							
		}
		
	}
}

?>