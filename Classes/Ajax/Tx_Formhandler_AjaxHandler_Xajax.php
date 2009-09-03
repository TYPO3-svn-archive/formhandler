<?php

class Tx_Formhandler_AjaxHandler_Xajax extends Tx_Formhandler_AbstractAjaxHandler {
	
	public function initAjax() {
		if(t3lib_extMgm::isLoaded('xajax', 0) && !class_exists('tx_xajax') && !$this->xajax) {
			require_once(t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');
		}
		if (!$this->xajax && class_exists('tx_xajax')) {
			$view = $this->componentManager->getComponent('Tx_Formhandler_View_Form');
			$validator = $this->componentManager->getComponent('Tx_Formhandler_Validator_Ajax');

			$this->xajax = t3lib_div::makeInstance('tx_xajax');
			$this->xajax->setFlag('decodeUTF8Input',true);
			$this->prefixId = 'Tx_Formhandler';
			$this->xajax->setCharEncoding('utf-8');
			#$this->xajax->setWrapperPrefix($this->prefixId);
			//$this->xajax->registerPreFunction("showLoadingAnimation");
			$this->xajax->register(XAJAX_FUNCTION, array($this->prefixId . '_removeUploadedFile', &$view, 'removeUploadedFile'));
			$this->xajax->register(XAJAX_FUNCTION, array($this->prefixId . '_validateAjax', &$validator, 'validateAjax'));
			
			// Do you wnat messages in the status bar?
			
			$this->xajax->setFlag('statusMessages',true);
			$this->xajax->configure('debug',false);
			// Turn only on during testing
			//$this->xajax->debugOff();
			
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
			$this->xajax->processRequest();
		}
	}
	
	public function fillAjaxMarkers(&$markers) {
		session_start();
		$settings = $_SESSION['formhandlerSettings']['settings'];

		$flexformValue = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], 'required_fields', 'sMISC');
		if($flexformValue) {
			$fields = t3lib_div::trimExplode(',', $flexformValue);

			if(is_array($settings['validators.'])) {

				// Searches the index of Tx_Formhandler_Validator_Default
				foreach ($settings['validators.'] as $index => $validator) {
					if ($validator['class'] == 'Tx_Formhandler_Validator_Default') {
						break;
					}
				}
			} else {
				$index = 1;
			}

			// Adds the value.
			foreach($fields as $field) {
				$settings['validators.'][$index . '.']['config.']['fieldConf.'][$field . '.']['errorCheck.'] = array();
				$settings['validators.'][$index . '.']['config.']['fieldConf.'][$field . '.']['errorCheck.']['1'] = 'required';
			}
		}

		
		
		$markers['###loading_js###'] = '
			<script type="text/javascript">
				function showLoading(fieldname) {
					var el = document.getElementById("loading_"+fieldname);
					if(el) {
						el.style.display = "inline";
					}
					var el1 = document.getElementById("error_"+fieldname);
					if(el1) {
						el1.style.display = "none";
					}
				}
			</script>
		';
		
		//parse validation settings
		if(is_array($settings['validators.'])) {
			foreach($settings['validators.'] as $key => $validatorSettings) {
				if(is_array($validatorSettings['config.']['fieldConf.'])) {
					foreach($validatorSettings['config.']['fieldConf.'] as $fieldname => $fieldSettings) {
						$replacedFieldname = str_replace('.', '', $fieldname);
						$markers['###validate_' . $replacedFieldname . '###'] = 'onblur="showLoading(\'' . $replacedFieldname .'\');xajax_' . $this->prefixId . '_validateAjax(\'' . $replacedFieldname . '\', this.value);"';
						$markers['###loading_' . $replacedFieldname . '###'] = '<span style="display:none" id="loading_' . $replacedFieldname . '"><img src="' . t3lib_extMgm::extRelPath('formhandler') . 'Resources/Images/ajax-loader.gif' . ' "/></span>';
					}
				}
			}
		}
		
		if(is_array($_SESSION['formhandlerFiles'])) {
			$singleWrap = $settings['singleFileMarkerTemplate.']['singleWrap'];
			$totalMarkerSingleWrap = $settings['totalFilesMarkerTemplate.']['singleWrap'];
			$totalWrap = $settings['singleFileMarkerTemplate.']['totalWrap'];
			$totalMarkersTotalWrap = $settings['totalFilesMarkerTemplate.']['totalWrap'];
			foreach($_SESSION['formhandlerFiles'] as $field => $files) {
				$markers['###' . $field . '_uploadedFiles###'] = '';
				$markers['###total_uploadedFiles###'] = '';
				foreach($files as $fileInfo) {
					$filename = $fileInfo['name'];
					$thumb = '';
					if($settings['singleFileMarkerTemplate.']['showThumbnails'] == '1') {
						$imgConf['image.'] = $settings['singleFileMarkerTemplate.']['image.'];
						$thumb = $this->getThumbnail($imgConf, $fileInfo);
					}
					if(t3lib_extMgm::isLoaded('xajax') && $settings['files.']['enableAjaxFileRemoval']) {
						$text = 'X';
						if($settings['files.']['customRemovalText']) {
							if($settings['files.']['customRemovalText.']) {
								$text = $this->cObj->cObjGetSingle($settings['files.']['customRemovalText'], $settings['files.']['customRemovalText.']);
							} else {
								$text = $settings['files.']['customRemovalText'];
							}
						}
						
						$link= '<a 
								href="javascript:void(0)" 
								class="formhandler_removelink" 
								onclick="xajax_' . $this->prefixId . '_removeUploadedFile(\'' . $field . '\',\'' . $fileInfo['uploaded_name'] . '\')"
								>' . $text . '</a>';
						$filename .= $link;
						$thumb .= $link;
					}
					if(strlen($singleWrap) > 0 && strstr($singleWrap, '|')) {
						$wrappedFilename = str_replace('|', $filename, $singleWrap);
						$wrappedThumb = str_replace('|', $thumb, $singleWrap);
					} else {
						$wrappedFilename = $filename;
						$wrappedThumb = $thumb;
					}
					if($settings['singleFileMarkerTemplate.']['showThumbnails'] == '1') {
						$markers['###' . $field . '_uploadedFiles###'] .= $wrappedThumb;
					} else {
						$markers['###' . $field . '_uploadedFiles###'] .= $wrappedFilename;
					}
					$filename = $fileInfo['name'];
					if($settings['totalFilesMarkerTemplate.']['showThumbnails'] == '1') {
						$imgConf['image.'] = $settings['totalFilesMarkerTemplate.']['image.'];
						if(!$imgconf['image.']) {
							$imgConf['image.'] = $settings['singleFileMarkerTemplate.']['image.'];
						}
						$thumb = $this->getThumbnail($imgConf, $fileInfo);
						
					}
					if(strlen($totalMarkerSingleWrap) > 0 && strstr($totalMarkerSingleWrap, '|')) {

						$wrappedFilename = str_replace('|', $filename, $totalMarkerSingleWrap);
						$wrappedThumb = str_replace('|', $thumb, $totalMarkerSingleWrap);
					} else {
						$wrappedFilename = $filename;
						$wrappedThumb = $thumb;
					}
				
					if($settings['totalFilesMarkerTemplate.']['showThumbnails'] == '1') {
						$markers['###total_uploadedFiles###'] .= $wrappedThumb;
					} else {
						$markers['###total_uploadedFiles###'] .= $wrappedFilename;
					}
				}
				if(strlen($totalWrap) > 0 && strstr($totalWrap,'|')) {
					$markers['###' . $field . '_uploadedFiles###'] = str_replace('|', $markers['###' . $field . '_uploadedFiles###'],$totalWrap);
				}
				$markers['###' . $field . '_uploadedFiles###'] = '<div id="Tx_Formhandler_UploadedFiles_' . $field . '">' . $markers['###' . $field . '_uploadedFiles###'] . '</div>';
			}
			if(strlen($totalMarkersTotalWrap) > 0 && strstr($totalMarkersTotalWrap, '|')) {
				$markers['###total_uploadedFiles###'] = str_replace('|', $markers['###total_uploadedFiles###'], $totalMarkersTotalWrap);
			}
			$markers['###TOTAL_UPLOADEDFILES###'] = $markers['###total_uploadedFiles###'];
			$markers['###total_uploadedfiles###'] = $markers['###total_uploadedFiles###'];
		}
	}
	
	
	
}

?>