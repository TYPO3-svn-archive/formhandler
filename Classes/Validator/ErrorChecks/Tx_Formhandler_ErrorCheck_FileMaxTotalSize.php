<?php

class Tx_Formhandler_ErrorCheck_FileMaxTotalSize extends Tx_Formhandler_AbstractErrorCheck {

	/**
	 * Validates that uploaded files has a maximum total file size
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	public function check(&$check, $name, &$gp) {
		$checkFailed = '';
		$maxSize = $this->utilityFuncs->getSingle($check['params'], 'maxTotalSize');
		$size = 0;

		// first we check earlier uploaded files
		$olderFiles = $this->globals->getSession()->get('files');
		foreach ((array) $olderFiles[$name] as $olderFile) {
			$size += intval($olderFile['size']);
		}

		// last we check currently uploaded file
		foreach ($_FILES as $sthg => &$files) {
			if (strlen($files['name'][$name]) > 0 &&
				$maxSize &&
				($size + intval($files['size'][$name])) > $maxSize) {

				unset($files);
				$checkFailed = $this->getCheckFailed($check);
			}
		}
		return $checkFailed;
	}

}
