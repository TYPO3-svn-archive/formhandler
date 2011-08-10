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
		foreach ((array) $olderFiles[$this->formFieldName] as $olderFile) {
			$size += intval($olderFile['size']);
		}

		// last we check currently uploaded file
		foreach ($_FILES as $sthg => &$files) {
			if (strlen($files['name'][$this->formFieldName]) > 0 &&
				$maxSize &&
				($size + intval($files['size'][$this->formFieldName])) > $maxSize) {

				unset($files);
				$checkFailed = $this->getCheckFailed();
			}
		}
		return $checkFailed;
	}

}
