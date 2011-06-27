<?php

class Tx_Formhandler_AjaxHandler_Jquery extends Tx_Formhandler_AbstractAjaxHandler {

	public function initAjax() {

	}

	public function fillAjaxMarkers(&$markers) {
		$settings = $this->globals->getSession()->get('settings');
		$initial = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'initial');
		
		$loadingImg = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'loading');
		if(strlen($loadingImg) === 0) {
			$loadingImg = t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ajax-loader.gif';
			$loadingImg = '<img src="' . $loadingImg . '"/>';
		}

		//parse validation settings
		if (is_array($settings['validators.']) && intval($settings['validators.']['disable']) !== 1) {
			foreach ($settings['validators.'] as $key => $validatorSettings) {
				if (is_array($validatorSettings['config.']['fieldConf.']) && intval($validatorSettings['config.']['disable']) !== 1) {
					foreach ($validatorSettings['config.']['fieldConf.'] as $fieldname => $fieldSettings) {
						$replacedFieldname = str_replace('.', '', $fieldname);
						$fieldname = $replacedFieldname;
						if ($this->globals->getFormValuesPrefix()) {
							$fieldname = $this->globals->getFormValuesPrefix() . '[' . $fieldname . ']';
						}
						$params = array(
							'eID' => 'formhandler',
							'pid' => $GLOBALS['TSFE']->id,
							'randomID' => $this->globals->getRandomID(),
							'field' => $replacedFieldname,
							'value' => ''
						);
						$url = $this->globals->getCObj()->getTypoLink_Url($GLOBALS['TSFE']->id, $params);
						$markers['###validate_' . $replacedFieldname . '###'] = '
							<span class="loading" id="loading_' . $replacedFieldname . '" style="display:none">' . $loadingImg . '</span>
							<span id="result_' . $replacedFieldname . '">' . str_replace('###fieldname###', $replacedFieldname, $initial) . '</span>
							<script type="text/javascript">
								$(document).ready(function() {
									$("*[name=\'' . $fieldname . '\']").blur(function() {
										var fieldVal = escape($(this).val());
										if ($(this).attr("type") == "radio" || $(this).attr("type") == "checkbox") {
											if ($(this).attr("checked") == "") {
												fieldVal = "";
											}
										}
										$("#loading_' . $replacedFieldname . '").show();
										$("#result_' . $replacedFieldname . '").hide();
										var url = "' . $url . '";
										url = url.replace("value=", "value=" + fieldVal);
										$("#result_' . $replacedFieldname . '").load(url,
										function() {
										
											$("#loading_' . $replacedFieldname . '").hide();
											$("#result_' . $replacedFieldname . '").show();
										});
									});
								});
							</script>
						';
					}
				}
			}
		}
	}

	public function getFileRemovalLink($text, $field, $uploadedFileName) {
		$params = array(
			'eID' => 'formhandler-removefile',
			'pid' => $GLOBALS['TSFE']->id,
			'field' => $field,
			'uploadedFileName' => $uploadedFileName,
			'randomID' => $this->globals->getRandomID()
		);
		$url = $this->globals->getCObj()->getTypoLink_Url($GLOBALS['TSFE']->id, $params);
		return '<a  
				class="formhandler_removelink" 
				href="' . $url . '"
				>' . $text . '</a>
				<script type="text/javascript">
					$(document).ready(function() {
						jQuery("a.formhandler_removelink").click(function() {
							var url = jQuery(this).attr("href");
							jQuery("#Tx_Formhandler_UploadedFiles_' . $field . '").load(url + "#Tx_Formhandler_UploadedFiles_picture");
							return false;
						});
					});
				</script>';
	}

}
?>