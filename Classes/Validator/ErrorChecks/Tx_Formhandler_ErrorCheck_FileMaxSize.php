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
 * Validates that an uploaded file has a maximum file size
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
class Tx_Formhandler_ErrorCheck_FileMaxSize extends Tx_Formhandler_AbstractErrorCheck {

	public function init($gp, $settings) {
		parent::init($gp, $settings);
		$this->mandatoryParameters = array('maxSize');
	}

	public function check() {
		$checkFailed = '';
		$maxSize = intval($this->utilityFuncs->getSingle($this->settings['params'], 'maxSize'));
		$phpIniUploadMaxFileSize = $this->utilityFuncs->convertBytes(ini_get('upload_max_filesize'));
		if($maxSize > $phpIniUploadMaxFileSize) {
			$this->utilityFuncs->throwException('error_check_filemaxsize', t3lib_div::formatSize($maxSize, ' Bytes| KB| MB| GB'), $this->formFieldName, t3lib_div::formatSize($phpIniUploadMaxFileSize, ' Bytes| KB| MB| GB'));
		}
		foreach ($_FILES as $sthg => &$files) {
			if (strlen($files['name'][$this->formFieldName]) > 0 &&
				$maxSize &&
				$files['size'][$this->formFieldName] > $maxSize) {

				unset($files);
				$checkFailed = $this->getCheckFailed();
			}
		}
		return $checkFailed;
	}

}
?>