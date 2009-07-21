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
 * An interceptor doing XSS checking on GET/POST parameters
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Interceptor
 */
class Tx_Formhandler_Interceptor_RemoveXSS extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @param array $gp The GET/POST parameters
	 * @param array $settings The defined TypoScript settings for the finisher
	 * @return array The probably modified GET/POST parameters
	 */
	public function process($gp, $settings) {

		return $this->sanitizeValues($gp);
	}

	/**
	 * This method does XSS checks and escapes malicious data
	 *
	 * @param array $values The GET/POST parameters
	 * @return array The sanitized GET/POST parameters
	 */
	public function sanitizeValues($values) {

		if(!is_array($values)) {
			return array();
		}

		//if removeXSS is part of the core API
		if (method_exists('t3lib_div','removeXSS')) {
			foreach ($values as $key => $value) {
				if(is_array($value)) {
					$sanitizedArray[$key] = $this->sanitizeValues($value);
				} else {
					$value = str_replace("\t", '', $value);
					$sanitizedArray[$key] = t3lib_div::removeXSS($value);
				}
			}
				
		//use the class provided by Formhandler
		} else {
			require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/RemoveXSS.php');
			foreach ($values as $key => $value) {
				if(is_array($value)) {
					$sanitizedArray[$key] = $this->sanitizeValues($value);
				} else {
					$value = str_replace("\t", '', $value);
					$sanitizedArray[$key] = RemoveXSS::doRemoveXSS($value);
						
				}
			}
		}
		return $sanitizedArray;
	}

}
?>
