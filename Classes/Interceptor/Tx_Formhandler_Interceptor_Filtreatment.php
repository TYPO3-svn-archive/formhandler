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
class Tx_Formhandler_Interceptor_Filtreatment extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$this->removeChars = array('<', '>', '"', "'");

		//search for a global setting for character removal
		$globalSetting = $this->settings['fieldConf.']['global.'];
		if ($globalSetting['removeChars']) {
			$sep = ',';

			//user set custom rules via cObject
			$cObjSettings = $globalSetting['removeChars.'];
			if (is_array($cObjSettings)) {
				$list = Tx_Formhandler_StaticFuncs::getSingle($globalSetting, 'removeChars');

				//user set custom separator
				if ($globalSetting['separator']) {
					$sep = $globalSetting['separator'];
				}
			} else {

				//user entered a comma seperated list
				$list = $globalSetting['removeChars'];
			}
			$this->removeChars = t3lib_div::trimExplode($sep, $list);
		} elseif (intval($globalSetting['removeChars.']['disable']) === 1) {

			//user disabled removal globally
			$this->removeChars = array();
		}
		$this->gp = $this->sanitizeValues($this->gp);
		return $this->gp;
	}

	/**
	 * This method does XSS checks and escapes malicious data
	 *
	 * @param array $values The GET/POST parameters
	 * @return array The sanitized GET/POST parameters
	 */
	public function sanitizeValues($values) {

		if (!is_array($values)) {
			return array();
		}

		if (!class_exists('Filtreatment')) {
			require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/filtreatment/Filtreatment.php');
		}
		$filter = new Filtreatment();
		foreach ($values as $key => $value) {
			if (is_array($value)) {
				$sanitizedArray[$key] = $this->sanitizeValues($value);
			} elseif (strlen(trim($value)) > 0)  {
				$removeChars = $this->removeChars;

				//search for a specific setting for this field
				$fieldSetting = $this->settings['fieldConf.'][$key . '.'];
				if ($fieldSetting['removeChars']) {
					$sep = ',';

					//user set custom rules via cObject
					$cObjSettings = $fieldSetting['removeChars.'];
					if (is_array($cObjSettings)) {
						$list = Tx_Formhandler_StaticFuncs::getSingle($fieldSetting, 'removeChars');

						//user set custom separator
						if ($fieldSetting['separator']) {
							$sep = $fieldSetting['separator'];
						}
					} else {

						//user entered a comma seperated list
						$list = $fieldSetting['removeChars'];
					}
					$removeChars = t3lib_div::trimExplode($sep, $list);
				} elseif (intval($fieldSetting['removeChars.']['disable']) === 1) {

					//user disabled removal for this field
					$removeChars = array();
				}

				$value = str_replace("\t", '', $value);
				$value = str_replace($removeChars, ' ', $value);

				$isUTF8 = $this->isUTF8($value);
				if (!$isUTF8) {
					$value = utf8_encode($value);
				}
				$value = $filter->ft_xss($value, 'UTF-8');

				if (!$isUTF8) {
					$value = utf8_decode($value);
				}
				$sanitizedArray[$key] = $value;
			} else {
				$sanitizedArray[$key] = $value;
			}
		}
		return $sanitizedArray;
	}

	/**
	 * This method detects if a given input string if valid UTF-8.
	 *
	 * @author hmdker <hmdker(at)gmail(dot)com>
	 * @param string
	 * @return boolean is UTF-8
	 */
	protected function isUTF8($str) {
		$c = 0;
		$b = 0;
		$bits = 0;
		$len = strlen($str);
		for($i = 0; $i < $len; $i++) {
			$c = ord($str[$i]);
			if ($c > 128){
				if (($c >= 254)) {
					return FALSE;
				} elseif ($c >= 252) {
					$bits = 6;
				} elseif ($c >= 248) {
					$bits = 5;
				} elseif ($c >= 240) {
					$bits = 4;
				} elseif ($c >= 224) {
					$bits = 3;
				} elseif ($c >= 192) {
					$bits = 2;
				} else {
					return FALSE;
				}
				if (($i + $bits) > $len) {
					return FALSE;
				}
				while($bits > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191) {
						return FALSE;
					}
					$bits--;
				}
			}
		}
		return TRUE;
	}

}
?>
