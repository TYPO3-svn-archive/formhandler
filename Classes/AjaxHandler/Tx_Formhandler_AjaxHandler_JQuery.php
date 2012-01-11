<?php

class Tx_Formhandler_AjaxHandler_Jquery extends Tx_Formhandler_AbstractAjaxHandler {

	public function initAjax() {
		$settings = $this->globals->getSession()->get('settings');
		$autoDisableSubmitButton = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'autoDisableSubmitButton');
		$js = '';
		if(intval($autoDisableSubmitButton) === 1) {
			$js .= '$(".form-invalid").attr("disabled", "disabled");';
		}
		$ajaxSubmit = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'ajaxSubmit');
		if(intval($ajaxSubmit) === 1) {
			$js .= '
			$(".Tx-Formhandler FORM").live("submit", function() {
				return false;
			});
			$(".Tx-Formhandler INPUT[type=\'submit\']").live("click", function() {
				$(".Tx-Formhandler INPUT[type=\'submit\']").attr("disabled", "disabled");
				var container = $(this).closest(".Tx-Formhandler");
				var form = $(this).closest("FORM");
				var requestURL = "/index.php?id=' . $GLOBALS['TSFE']->id . '&eID=formhandler-ajaxsubmit&randomID=' . $this->globals->getRandomID() . '";
				var postData = form.serialize() + "&" + $(this).attr("name") + "=submit";
				container.find(".loading_ajax-submit").show();
				$.ajax({
				    type: "post",
				    url: requestURL,
				    data: postData,
				    dataType: "json",
				    success: function(data, textStatus) {
				        if (data.redirect) {
				            window.location.href = data.redirect;
				        }
				        else {
				            form.closest(".Tx-Formhandler").replaceWith(data.form);
				        }
				    }
				});
				return false;
			});';
		}
		if(strlen($js) > 0) {
			$GLOBALS['TSFE']->additionalHeaderData['Tx_Formhandler_AjaxHandler_Jquery'] = '
				<script type="text/javascript">
				$(function() {
				' . $js . '
				});
				</script>
			';
		}
	}

	public function fillAjaxMarkers(&$markers) {
		$settings = $this->globals->getSession()->get('settings');
		$initial = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'initial');
		
		$loadingImg = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'loading');
		if(strlen($loadingImg) === 0) {
			$loadingImg = t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ajax-loader.gif';
			$loadingImg = '<img src="' . $loadingImg . '"/>';
		}
		
		
		$autoDisableSubmitButton = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'autoDisableSubmitButton');
		if(intval($autoDisableSubmitButton) === 1) {
			$markers['###validation-status###'] = 'formhandler-validation-status form-invalid';
		}
		
		$ajaxSubmit = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'ajaxSubmit');

		if(intval($ajaxSubmit) === 1) {
			$ajaxSubmitLoader = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'ajaxSubmitLoader');
			if(strlen($ajaxSubmitLoader) === 0) {
				$loadingImg = t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ajax-loader.gif';
				$loadingImg = '<img src="' . $loadingImg . '"/>';
				$ajaxSubmitLoader = '<span class="loading_ajax-submit">' . $loadingImg . '</span>';
			}
			$markers['###loading_ajax-submit###'] = $ajaxSubmitLoader;
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
							<span id="result_' . $replacedFieldname . '" class="formhandler-ajax-validation-result">' . str_replace('###fieldname###', $replacedFieldname, $initial) . '</span>
							<script type="text/javascript">
								$(function() {
									$("*[name=\'' . $fieldname . '\']").blur(function() {
										var fieldVal = escape($(this).val());
										if ($(this).attr("type") == "radio" || $(this).attr("type") == "checkbox") {
											if ($(this).attr("checked") == "") {
												fieldVal = "";
											}
										}
										
										var loading = $("#loading_' . $replacedFieldname . '");
										var result = $("#result_' . $replacedFieldname . '");
										
										loading.show();
										result.hide();
										var url = "' . $url . '";
										url = url.replace("value=", "value=" + fieldVal);
										result.load(url, function() {
											loading.hide();
											result.show();
						';
						if(intval($autoDisableSubmitButton) === 1) {
							$markers['###validate_' . $replacedFieldname . '###'] .= '
								if(result.find("SPAN.error").length > 0) {
									result.data("isValid", false);
								} else {
									result.data("isValid", true);
								}
								var valid = true;
								$("#' . $this->globals->getFormID() . ' .formhandler-ajax-validation-result").each(function() {
									if(!$(this).data("isValid")) {
										valid = false;
									}
								});
								var button = $("#' . $this->globals->getFormID() . ' INPUT.formhandler-validation-status");
								if(valid) {
									button.removeAttr("disabled");
									button.removeClass("form-invalid").addClass("form-valid");
								} else {
									button.attr("disabled", "disabled");
									button.removeClass("form-valid").addClass("form-invalid");
								}
							';
						}
						$markers['###validate_' . $replacedFieldname . '###'] .= '
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