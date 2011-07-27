<?php

class Tx_Formhandler_ErrorCheck_FileMaxTotalSize extends Tx_Formhandler_AbstractErrorCheck {

	public function init($gp, $settings) {
		parent::init($gp, $settings);
		$this->mandatoryParameters = array('maxTotalSize');
	}

	public function check() {
		$checkFailed = '';
		$maxSize = $this->utilityFuncs->getSingle($this->settings['params'], 'maxTotalSize');
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
				$checkFailed = $this->getCheckFailed();
			}
		}
		return $checkFailed;
	}

}
