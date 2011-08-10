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
 * Validates that an uploaded file via specified field matches one of the given file types
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_FileAllowedTypes extends Tx_Formhandler_AbstractErrorCheck {

	public function init($gp, $settings) {
		parent::init($gp, $settings);
		$this->mandatoryParameters = array('allowedTypes');
	}

	public function check() {
		$checkFailed = '';
		$allowed = $this->utilityFuncs->getSingle($this->settings['params'], 'allowedTypes');
		foreach ($_FILES as $sthg => &$files) {
			if (strlen($files['name'][$this->formFieldName]) > 0) {
				if ($allowed) {
					$types = t3lib_div::trimExplode(',', $allowed);
					$fileext = substr($files['name'][$this->formFieldName], strrpos($files['name'][$this->formFieldName], '.') + 1);
					$fileext = strtolower($fileext);
					if (!in_array($fileext, $types)) {
						unset($files);
						$checkFailed = $this->getCheckFailed();
					}
				}
			}
		}
		return $checkFailed;
	}

}
?>