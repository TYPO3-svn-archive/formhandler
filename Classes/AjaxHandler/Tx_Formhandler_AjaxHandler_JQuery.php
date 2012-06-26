<?php
/*                                                                       *
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
*                                                                        */

/**
 * Implementation of an AjaxHandler using the jQuery Framework.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_AjaxHandler_Jquery extends Tx_Formhandler_AbstractAjaxHandler {

	/**
	 * The alias for the famous "$"
	 * 
	 * @access protected
	 * @var string
	 */
	protected $jQueryAlias;

	/**
	 * Selector string for the submit button of the form
	 *
	 * @access protected
	 * @var string
	 */
	protected $submitButtonSelector;

	/**
	 * Array holding the CSS classes to set for the various states of AJAX validation
	 *
	 * @access protected
	 * @var array
	 */
	protected $validationStatusClasses;

	/**
	 * Initialize AJAX stuff
	 *
	 * @return void
	 */
	public function initAjax() {
		$settings = $this->globals->getSession()->get('settings');
		$this->jQueryAlias = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'alias');
		if(strlen(trim($this->jQueryAlias)) === 0) {
			$this->jQueryAlias = 'jQuery';
		}
		
		$this->submitButtonSelector = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'submitButtonSelector');
		if(strlen(trim($this->submitButtonSelector)) === 0) {
			$this->submitButtonSelector = '.Tx-Formhandler INPUT[type=\'submit\']';
		}
		$this->submitButtonSelector = str_replace('"', '\"', $this->submitButtonSelector);

		$this->validationStatusClasses = array(
			'base' => 'formhandler-validation-status',
			'valid' => 'form-valid',
			'invalid' => 'form-invalid'
		);
		if(is_array($settings['ajax.']['config.']['validationStatusClasses.'])) {
			if($settings['ajax.']['config.']['validationStatusClasses.']['base']) {
				$this->validationStatusClasses['base'] = $this->utilityFuncs->getSingle($settings['ajax.']['config.']['validationStatusClasses.'], 'base');
			}
			if($settings['ajax.']['config.']['validationStatusClasses.']['valid']) {
				$this->validationStatusClasses['valid'] = $this->utilityFuncs->getSingle($settings['ajax.']['config.']['validationStatusClasses.'], 'valid');
			}
			if($settings['ajax.']['config.']['validationStatusClasses.']['invalid']) {
				$this->validationStatusClasses['invalid'] = $this->utilityFuncs->getSingle($settings['ajax.']['config.']['validationStatusClasses.'], 'invalid');
			}
		}

		$autoDisableSubmitButton = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'autoDisableSubmitButton');
		$js = '';
		if(intval($autoDisableSubmitButton) === 1) {
			$js .= '' . $this->jQueryAlias . '(".form-invalid").attr("disabled", "disabled");';
		}
		$ajaxSubmit = $this->utilityFuncs->getSingle($settings['ajax.']['config.'], 'ajaxSubmit');
		if(intval($ajaxSubmit) === 1) {
			$js .= '
			' . $this->jQueryAlias . '(".Tx-Formhandler FORM").live("submit", function() {
				return false;
			});
			' . $this->jQueryAlias . '("' . $this->submitButtonSelector . '").live("click", function() {
				' . $this->jQueryAlias . '("' . $this->submitButtonSelector . '").attr("disabled", "disabled");
				var container = ' . $this->jQueryAlias . '(this).closest(".Tx-Formhandler");
				var form = ' . $this->jQueryAlias . '(this).closest("FORM");
				var requestURL = "/index.php?id=' . $GLOBALS['TSFE']->id . '&eID=formhandler-ajaxsubmit&randomID=' . $this->globals->getRandomID() . '";
				var postData = form.serialize() + "&" + ' . $this->jQueryAlias . '(this).attr("name") + "=submit";
				container.find(".loading_ajax-submit").show();
				jQuery.ajax({
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
				' . $this->jQueryAlias . '(function() {
				' . $js . '
				});
				</script>
			';
		}
	}

	/**
	 * Method called by the view to let the AjaxHandler add its markers.
	 *
	 * The view passes the marker array by reference.
	 *
	 * @param array &$markers Reference to the marker array
	 * @return void
	 */
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
			$markers['###validation-status###'] = $this->validationStatusClasses['base'] . ' ' . $this->validationStatusClasses['invalid'];
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
		if (is_array($settings['validators.']) && intval($this->utilityFuncs->getSingle($settings['validators.'],'disable')) !== 1) {
			foreach ($settings['validators.'] as $key => $validatorSettings) {
				if (is_array($validatorSettings['config.']['fieldConf.']) && intval($this->utilityFuncs->getSingle($validatorSettings['config.'], 'disable')) !== 1) {
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
								' . $this->jQueryAlias . '(function() {
									' . $this->jQueryAlias . '("*[name=\'' . $fieldname . '\']").blur(function() {
										var field = ' . $this->jQueryAlias . '(this);
										var fieldVal = encodeURIComponent(field.val());
										if(field.attr("type") == "radio" || field.attr("type") == "checkbox") {
											if (field.attr("checked") == "") {
												fieldVal = "";
											}
										}
										var loading = ' . $this->jQueryAlias . '("#loading_' . $replacedFieldname . '");
										var result = ' . $this->jQueryAlias . '("#result_' . $replacedFieldname . '");
										loading.show();
										result.hide();
										var url = "' . $url . '";
						';
						if($validatorSettings['config.']['fieldConf.'][$replacedFieldname . '.']['errorCheck.']) {
							foreach($validatorSettings['config.']['fieldConf.'][$replacedFieldname . '.']['errorCheck.'] as $key => $errorCheck) {
								if($errorCheck === 'equalsField') {
									$equalsField = $this->utilityFuncs->getSingle($validatorSettings['config.']['fieldConf.'][$replacedFieldname . '.']['errorCheck.'][$key . '.'], 'field');
									if(strlen(trim($equalsField)) > 0) {
										$equalsFieldName = $equalsField;
										if ($this->globals->getFormValuesPrefix()) {
											$equalsFieldName = $this->globals->getFormValuesPrefix() . '[' . $equalsField . ']';
										}
										$markers['###validate_' . $replacedFieldname . '###'] .= '
											var equalsField = ' . $this->jQueryAlias . '("*[name=\'' . $equalsFieldName . '\']");
											var equalsFieldVal = encodeURIComponent(equalsField.val());
											if (equalsField.attr("type") == "radio" || equalsField.attr("type") == "checkbox") {
												if (equalsField.attr("checked") == "") {
													equalsFieldVal = "";
												}
											}
											url += "&equalsFieldName=' . urlencode($equalsField) . '&equalsFieldValue=" + equalsFieldVal;
										';
									}
								}
							}
						}
						$markers['###validate_' . $replacedFieldname . '###'] .= '
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
								' . $this->jQueryAlias . '("#' . $this->globals->getFormID() . ' .formhandler-ajax-validation-result").each(function() {
									if(!' . $this->jQueryAlias . '(this).data("isValid")) {
										valid = false;
									}
								});
								var button = ' . $this->jQueryAlias . '("#' . $this->globals->getFormID() . ' INPUT.' . $this->validationStatusClasses['base'] . '");
								if(valid) {
									button.removeAttr("disabled");
									button.removeClass("' . $this->validationStatusClasses['invalid'] . '").addClass("' . $this->validationStatusClasses['valid'] . '");
								} else {
									button.attr("disabled", "disabled");
									button.removeClass("' . $this->validationStatusClasses['valid'] . '").addClass("' . $this->validationStatusClasses['invalid'] . '");
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

	/**
	 * Method called by the view to get an AJAX based file removal link.
	 *
	 * @param string $text The link text to be used
	 * @param string $field The field name of the form field
	 * @param string $uploadedFileName The name of the file to be deleted
	 * @return void
	 */
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
					' . $this->jQueryAlias . '(function() {
						' . $this->jQueryAlias . '("a.formhandler_removelink").click(function() {
							var url = ' . $this->jQueryAlias . '(this).attr("href");
							' . $this->jQueryAlias . '("#Tx_Formhandler_UploadedFiles_' . $field . '").load(url + "#Tx_Formhandler_UploadedFiles_picture");
							return false;
						});
					});
				</script>';
	}

}
?>