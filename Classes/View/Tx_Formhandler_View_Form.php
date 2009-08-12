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
 * A default view for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	View
 */
class Tx_Formhandler_View_Form extends Tx_Formhandler_AbstractView {

	/**
	 * Removes an uploaded file from $_SESSION. This method is called via an AJAX request.
	 *
	 * @param string $fieldname The field holding the file to delete
	 * @param string $filename The file to delete
	 * @return void
	 */
	public function removeUploadedFile($fieldname,$filename) {
		if(!t3lib_extMgm::isLoaded('xajax')) {
			return;
		}

		if(!class_exists('tx_xajax_response')) {
			// Instantiate the tx_xajax_response object
			require (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax_response.php');
		}
		
		$objResponse = new tx_xajax_response();

		session_start();

		if(is_array($_SESSION['formhandlerFiles'])) {
			foreach($_SESSION['formhandlerFiles'] as $field => $files) {

				if(!strcmp($field,$fieldname)) {
					foreach($files as $key=>&$fileInfo) {
						if(!strcmp($fileInfo['uploaded_name'], $filename)) {
							unset($_SESSION['formhandlerFiles'][$field][$key]);
						}
					}
				}
			}
		}

		// Add the content to or Result Box: #formResult
		if(is_array($_SESSION['formhandlerFiles'])) {
			$markers = array();
			$this->fillFileMarkers($markers);
			$content = $markers['###'. $fieldname. '_uploadedFiles###'];
			$objResponse->addAssign('Tx_Formhandler_UploadedFiles_' . $fieldname, 'innerHTML', $content);

		} else {
			$objResponse->addAssign('Tx_Formhandler_UploadedFiles_' . $fieldname, 'innerHTML', '');
		}

		//return the XML response
		return $objResponse->getXML();
	}


	/**
	 * Main method called by the controller.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $errors The errors occurred in validation
	 * @return string content
	 */
	public function render($gp,$errors) {

		session_start();
		
		//set GET/POST parameters
		/*$this->gp = array();
		foreach($gp as $k=>&$v) {
			$this->gp[$k] = $v;
			if(is_array)
		}*/
		$this->gp = $gp;

		//set template
		$this->template = $this->subparts['template'];

		//set settings
		$this->settings = $this->parseSettings();

		$this->errors = $errors;

		//set language file
		if(!$this->langFile) {
			$this->readLangFile();
		}
		
		if(!$this->gp['submitted']) {
			$this->storeStartEndBlock();
		} elseif($_SESSION['formhandlerSettings']['currentStep'] != 1) {
			$this->fillStartEndBlock();
		}
		
		//fill LLL:[language_key] markers
		$this->fillLangMarkers();

		//substitute ISSET markers
		$this->substituteIssetSubparts();

		//fill Typoscript markers
		if(is_array($this->settings['markers.'])) {
			$this->fillTypoScriptMarkers();
		}

		//fill default markers
		$this->fillDefaultMarkers();

		//fill value_[fieldname] markers
		$this->fillValueMarkers();

		//fill selected_[fieldname]_value markers and checked_[fieldname]_value markers
		$this->fillSelectedMarkers();

		//fill LLL:[language_key] markers again to make language markers in other markers possible
		$this->fillLangMarkers();

		//fill error_[fieldname] markers
		if(!empty($errors)) {
			$this->fillErrorMarkers($errors);
			$this->fillIsErrorMarkers($errors);
		}

		//remove markers that were not substituted
		$content = Tx_Formhandler_StaticFuncs::removeUnfilledMarkers($this->template);

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Reads the translation file entered in TS setup.
	 *
	 * @return void
	 */
	protected function readLangFile() {
		if(is_array($this->settings['langFile.'])) {
			$this->langFile = $this->cObj->cObjGetSingle($this->settings['langFile'], $this->settings['langFile.']);
		} else {
			$this->langFile = Tx_Formhandler_StaticFuncs::resolveRelPathFromSiteRoot($this->settings['langFile']);
		}
	}

	/**
	 * Helper method used by substituteIssetSubparts()
	 *
	 * @see Tx_Formhandler_StaticFuncs::substituteIssetSubparts()
	 * @author  Stephan Bauer <stephan_bauer(at)gmx.de>
	 * @return boolean
	 */
	protected function markersCountAsSet($conditionValue) {

		// Find first || or && or !
		$pattern = '/(_*([A-Za-z0-9]+)_*(\|\||&&)_*([^_]+)_*)|(_*(!)_*([A-Za-z0-9]+))/';

		session_start();
		// recurse if there are more
		if( preg_match($pattern, $conditionValue, $matches) ){
			$isset = isset($this->gp[$matches[2]]);
			if($matches[3] == '||' && $isset) {
				$return = true;
			} elseif($matches[3] == '||' && !$isset) {
				$return = $this->markersCountAsSet($matches[4]);
			} elseif($matches[3] == '&&' && $isset) {
				$return = $this->markersCountAsSet($matches[4]);
			} elseif($matches[3] == '&&' && !$isset) {
				$return = false;
			} elseif($matches[6] == '!' && !$isset) {
				return !(isset($this->gp[$matches[7]]) && $this->gp[$matches[7]] != '');
			} elseif($_SESSION['formhandlerSettings']['debugMode'] == 1) {
				Tx_Formhandler_StaticFuncs::debugMessage('invalid_isset', $matches[2]);
			}
		} else {

			// end of recursion
			$return = isset($this->gp[$conditionValue]) && ($this->gp[$conditionValue] != '');
		}
		return $return;
	}

	/**
	 * Use or remove subparts with ISSET_[fieldname] patterns (thx to Stephan Bauer <stephan_bauer(at)gmx.de>)
	 *
	 * @author  Stephan Bauer <stephan_bauer(at)gmx.de>
	 * @return	string		substituted HTML content
	 */
	protected function substituteIssetSubparts(){
		$flags = array();
		$nowrite = false;
		$out = array();
		foreach(split(chr(10), $this->template) as $line){

			// works only on it's own line
			$pattern = '/###isset_+([^#]*)_*###/i';

			// set for odd ISSET_xyz, else reset
			if(preg_match($pattern, $line, $matches)) {
				if(!$flags[$matches[1]]) { // set
					$flags[$matches[1]] = true;

					// set nowrite flag if required until the next ISSET_xyz
					// (only if not already set by envelop)
					if((!$this->markersCountAsSet($matches[1])) && (!$nowrite)) {
						$nowrite = $matches[1];
					}
				} else { // close it
					$flags[$matches[1]] = false;
					if($nowrite == $matches[1]) {
						$nowrite = 0;
					}
				}
			} else { // It is no ISSET_line. Write if permission is given.
				if(!$nowrite) {
					$out[] = $line;
				}
			}
		}

		$out = implode(chr(10),$out);

		$this->template = $out;
	}

	/**
	 * Copies the subparts ###FORM_STARTBLOCK### and ###FORM_ENDBLOCK### and stored them in $_SESSION.
	 * This is needed to replace the markers ###FORM_STARTBLOCK### and ###FORM_ENDBLOCK### in the next steps.
	 *
	 * @return void
	 */
	protected function storeStartEndBlock() {
		session_start();
		if(!isset($_SESSION['startblock']) || empty($_SESSION['startblock'])) {
			$_SESSION['startblock'] = $this->cObj->getSubpart($this->template, '###FORM_STARTBLOCK###');
		}
		if(!isset($_SESSION['endblock']) || empty($_SESSION['endblock'])) {
			$_SESSION['endblock'] = $this->cObj->getSubpart($this->template, '###FORM_ENDBLOCK###');
		}
	}

	/**
	 * Fills the markers ###FORM_STARTBLOCK### and ###FORM_ENDBLOCK### with the stored values from $_SESSION.
	 *
	 * @return void
	 */
	protected function fillStartEndBlock() {
		session_start();
		$markers = array (
			'###FORM_STARTBLOCK###' => $_SESSION['startblock'],
			'###FORM_ENDBLOCK###' => $_SESSION['endblock']
		);

		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}

	/**
	 * Returns the global TypoScript settings of Formhandler
	 *
	 * @return array The settings
	 */
	protected function parseSettings() {
		session_start();
		return $_SESSION['formhandlerSettings']['settings'];
	}

	/**
	 * Adds the values stored in $_SESSION as hidden fields in marker ###ADDITIONAL_MULTISTEP###.
	 *
	 * Needed in conditional forms.
	 *
	 * @param	array	&$markers The markers to put the new one into
	 * @return 	void
	 */
	protected function addHiddenFields(&$markers) {
		session_start();
		$hiddenFields = '';

		if(is_array($_SESSION['formhandlerValues'])) {
			foreach($_SESSION['formhandlerValues'] as $step => $params) {
				if($step != $_SESSION['formhandlerSettings']['currentStep']) {
					foreach($params as $key=>$value) {
						$name = $key;
						if($_SESSION['formhandlerSettings']['formValuesPrefix']) {
							$name = $_SESSION['formhandlerSettings']['formValuesPrefix'] . '[' . $key . ']';
						}
						if(is_array($value)) {
							foreach($value as $k => $v) {

								$hiddenFields .= '<input type="hidden" name="' . $name . '[]" value="' . $v . '" />';
							}
						} else {
							$hiddenFields .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
						}
					}
				}
			}
		}
		$markers['###ADDITIONAL_MULTISTEP###'] = $hiddenFields;
	}

	/**
	 * Substitutes markers
	 * 		###selected_[fieldname]_[value]###
	 * 		###checked_[fieldname]_[value]###
	 * in $this->template
	 *
	 * @return void
	 */
	protected function fillSelectedMarkers() {
		$markers = array();
		if (is_array($this->gp)) {
			foreach($this->gp as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $field => $value) {
						$markers['###checked_' . $k . '_' . $value . '###'] = 'checked="checked"';
						$markers['###selected_' . $k . '_' . $value . '###'] = 'selected="selected"';
					}
				} else {
					$markers['###checked_' . $k  .'_' . $v . '###'] = 'checked="checked"';
					$markers['###selected_' . $k . '_' . $v . '###'] = 'selected="selected"';
				}
			}
			$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
		}
	}

	/**
	 * Substitutes default markers in $this->template.
	 *
	 * @return void
	 */
	protected function fillDefaultMarkers() {
		$settings = $this->parseSettings();
		$parameters = t3lib_div::_GET();
		if (isset($parameters['id'])) {
			unset($parameters['id']);
		}
		$path = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $parameters);
		$markers = array();
		$markers['###REL_URL###'] = $path;
		$markers['###TIMESTAMP###'] = time();
		$markers['###ABS_URL###'] = t3lib_div::locationHeaderUrl('') . $path;
		
		if($this->gp['generated_authCode']) {
			$markers['###auth_code###'] = $this->gp['generated_authCode'];
		}
		
		$markers['###ip###'] = t3lib_div::getIndpEnv('REMOTE_ADDR');
		$markers['###submission_date###'] = date('d.m.Y H:i:s', time());
		$markers['###pid###'] = $GLOBALS['TSFE']->id;
		session_start();

		// current step
		$markers['###curStep###'] = $_SESSION['formhandlerSettings']['currentStep'];

		// maximum step/number of steps
		$markers['###maxStep###'] = $_SESSION['formhandlerSettings']['totalSteps'];

		// the last step shown
		$markers['###lastStep###'] = $_SESSION['formhandlerSettings']['lastStep'];

		$name = 'step-';
		if($_SESSION['formhandlerSettings']['formValuesPrefix']) {
			$name = $_SESSION['formhandlerSettings']['formValuesPrefix'] . '[' . $name . '#step#]';
		} else {
			$name = 'step-#step#';
		}

		// submit name for next page
		$markers['###submit_nextStep###'] = ' name="' . str_replace('#step#', ($_SESSION['formhandlerSettings']['currentStep'] + 1), $name) . '" ';

		// submit name for previous page
		$markers['###submit_prevStep###'] = ' name="' . str_replace('#step#', ($_SESSION['formhandlerSettings']['currentStep'] - 1), $name) . '" ';

		// submit name for reloading the same page/step
		$markers['###submit_reload###'] = ' name="' . str_replace('#step#',($_SESSION['formhandlerSettings']['currentStep']), $name) . '" ';

		// step bar
		$markers['###step_bar###'] = $this->createStepBar(
			$_SESSION['formhandlerSettings']['currentStep'],
			$_SESSION['formhandlerSettings']['totalSteps'],
			str_replace("#step#",($_SESSION['formhandlerSettings']['currentStep']-1),$name),
			str_replace("#step#",($_SESSION['formhandlerSettings']['currentStep']+1),$name)
		);

		$this->addHiddenFields($markers);
		$this->fillCaptchaMarkers($markers);
		$this->fillFEUserMarkers($markers);
		$this->fillFileMarkers($markers);

		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}

	/**
	 * Fills the markers for the supported captcha extensions.
	 *
	 * @param array &$markers Reference to the markers array
	 * @return void
	 */
	protected function fillCaptchaMarkers(&$markers) {
		global $LANG;
		#print "asd";
		if (t3lib_extMgm::isLoaded('captcha')){
			
			$markers['###CAPTCHA###'] = '<img src="' . t3lib_extMgm::siteRelPath('captcha') . 'captcha/captcha.php" alt="" />';
			$markers['###captcha###'] = $markers['###CAPTCHA###'];
		}
		if (t3lib_extMgm::isLoaded('simple_captcha')) {
			require_once(t3lib_extMgm::extPath('simple_captcha') . 'class.tx_simplecaptcha.php');
			$simpleCaptcha_className = t3lib_div::makeInstanceClassName('tx_simplecaptcha');
			$this->simpleCaptcha = new $simpleCaptcha_className();
			$captcha = $this->simpleCaptcha->getCaptcha();
			$markers['###simple_captcha###'] = $captcha;
			$markers['###SIMPLE_CAPTCHA###'] = $captcha;
		}
		if (t3lib_extMgm::isLoaded('sr_freecap')){
			require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
			$this->freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
			$markers = array_merge($markers,$this->freeCap->makeCaptcha());
		}
		if (t3lib_extMgm::isLoaded('jm_recaptcha')) {
			require_once(t3lib_extMgm::extPath('jm_recaptcha') . 'class.tx_jmrecaptcha.php');
			$this->recaptcha = new tx_jmrecaptcha();
			$markers['###RECAPTCHA###'] = $this->recaptcha->getReCaptcha();
			$markers['###recaptcha###'] = $markers['###RECAPTCHA###'];
		}

		if (t3lib_extMgm::isLoaded('wt_calculating_captcha')) {
			require_once(t3lib_extMgm::extPath('wt_calculating_captcha') . 'class.tx_wtcalculatingcaptcha.php');

			$captcha = t3lib_div::makeInstance('tx_wtcalculatingcaptcha');

			$markers['###WT_CALCULATING_CAPTCHA###'] = $captcha->generateCaptcha();
			$markers['###wt_calculating_captcha###'] = $markers['###WT_CALCULATING_CAPTCHA###'];
		}
		
		if (t3lib_extMgm::isLoaded('mathguard')) {
			require_once(t3lib_extMgm::extPath('mathguard') . 'class.tx_mathguard.php');

			$captcha = t3lib_div::makeInstance('tx_mathguard');
			$markers['###MATHGUARD###'] = $captcha->getCaptcha();
			$markers['###mathguard###'] = $markers['###MATHGUARD###'];
		}
	}

	/**
	 * Fills the markers ###FEUSER_[property]### with the data from $GLOBALS["TSFE"]->fe_user->user.
	 *
	 * @param array &$markers Reference to the markers array
	 * @return void
	 */
	protected function fillFEUserMarkers(&$markers) {
		if (is_array($GLOBALS["TSFE"]->fe_user->user)) {
			foreach($GLOBALS["TSFE"]->fe_user->user as $k => $v) {
				$markers['###FEUSER_' . strtoupper($k) . '###'] = $v;
				$markers['###FEUSER_' . strtolower($k) . '###'] = $v;
				$markers['###feuser_' . strtoupper($k) . '###'] = $v;
				$markers['###feuser_' . strtolower($k) . '###'] = $v;
			}
		}
	}

	/**
	 * Fills the file specific markers:
	 *
	 *  ###[fieldname]_minSize###
	 *  ###[fieldname]_maxSize###
	 *  ###[fieldname]_allowedTypes###
	 *  ###[fieldname]_maxCount###
	 *  ###[fieldname]_fileCount###
	 *  ###[fieldname]_remainingCount###
	 *
	 *  ###[fieldname]_uploadedFiles###
	 *  ###total_uploadedFiles###
	 *
	 * @param array &$markers Reference to the markers array
	 * @return void
	 */
	protected function fillFileMarkers(&$markers) {
		session_start();
		$settings = $this->parseSettings();

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

		//parse validation settings
		if(is_array($settings['validators.'])) {
			foreach($settings['validators.'] as $key => $validatorSettings) {
				if(is_array($validatorSettings['config.']['fieldConf.'])) {
					foreach($validatorSettings['config.']['fieldConf.'] as $fieldname => $fieldSettings) {
						$replacedFieldname = str_replace('.', '', $fieldname);
						if(is_array($fieldSettings['errorCheck.'])) {
							foreach($fieldSettings['errorCheck.'] as $key => $check) {
								switch($check) {
									case 'fileMinSize':
										$minSize = $fieldSettings['errorCheck.'][$key . '.']['minSize'];
										$markers['###' . $replacedFieldname . '_minSize###'] = t3lib_div::formatSize($minSize, ' Bytes | KB | MB | GB');
										break;
									case 'fileMaxSize':
										$maxSize = $fieldSettings['errorCheck.'][$key . '.']['maxSize'];
										$markers['###' . $replacedFieldname . '_maxSize###'] = t3lib_div::formatSize($maxSize, ' Bytes | KB | MB | GB');
										break;
									case 'fileAllowedTypes':
										$types = $fieldSettings['errorCheck.'][$key . '.']['allowedTypes'];
										$markers['###' . $replacedFieldname . '_allowedTypes###'] = $types;
										break;
									case 'fileMaxCount':
										$maxCount = $fieldSettings['errorCheck.'][$key . '.']['maxCount'];
										$markers['###' . $replacedFieldname . '_maxCount###'] = $maxCount;
											
										$fileCount = count($_SESSION['formhandlerFiles'][str_replace( '.', '', $fieldname)]);
										$markers['###' . $replacedFieldname . '_fileCount###'] = $fileCount;
											
										$remaining = $maxCount - $fileCount;
										$markers['###' . $replacedFieldname . '_remainingCount###'] = $remaining;
										break;
									case 'fileMinCount':
										$minCount = $fieldSettings['errorCheck.'][$key.'.']['minCount'];
										$markers['###' . $replacedFieldname . '_minCount###'] = $minCount;
										break;
									case 'required':
										$requiredSign = '*';
										if(isset($settings['requiredSign'])) {
											$requiredSign = $settings['requiredSign'];
										}
										$markers['###required_' . $replacedFieldname . '###'] = $requiredSign;
										break;
								}
							}
						}
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
				
			$requiredSign = '*';
			if(isset($settings['requiredSign'])) {
				$requiredSign = $settings['requiredSign'];
			}
			$markers['###required###'] = $requiredSign;
			$markers['###REQUIRED###'] = $markers['###required###'];
		}
	}
	
	protected function getThumbnail(&$imgConf, &$fileInfo) {
		$filename = $fileInfo['name'];
		$imgConf['image'] = 'IMAGE';
		if(!$imgConf['image.']['altText']) {
			$imgConf['image.']['altText'] = $filename;
		}
		if(!$imgConf['image.']['titleText']) {
			$imgConf['image.']['titleText'] = $filename;
		}

		$relPath = substr(($fileInfo['uploaded_folder'] . $fileInfo['uploaded_name']), 1);
		
		$imgConf['image.']['file'] = $relPath;
		
		if(!$imgConf['image.']['file.']['width'] && !$imgConf['image.']['file.']['height']) {
			$imgConf['image.']['file.']['width'] = '100m';
			$imgConf['image.']['file.']['height'] = '100m';
		}
		$thumb = $this->cObj->IMAGE($imgConf['image.']);
		return $thumb;
	}

	/**
	 * Substitutes markers
	 * 		###is_error_[fieldname]###
	 * 		###is_error###
	 * in $this->template
	 *
	 * @return void
	 */
	protected function fillIsErrorMarkers(&$errors) {

		$markers = array();
		foreach($errors as $field => $types) {
				
			if($this->settings['isErrorMarker.'][$field]) {
				if($this->settings['isErrorMarker.'][$field . '.']) {
					$errorMessage = $this->cObj->cObjGetSingle($this->settings['isErrorMarker.'][$field], $this->settings['isErrorMarker.'][$field . '.']);
				} else {
					$errorMessage = $this->settings['isErrorMarker.'][$field];
				}
			} elseif(strlen(trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error_' . $field))) > 0) {
				$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error_' . $field));
			} elseif (strlen(trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error'))) > 0) {
				$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error'));
			} elseif($this->settings['isErrorMarker.']['global']) {
				if($this->settings['isErrorMarker.']['global.']) {
					$errorMessage = $this->cObj->cObjGetSingle($this->settings['isErrorMarker.']['global'], $this->settings['isErrorMarker.']['global.']);
				} else {
					$errorMessage = $this->settings['isErrorMarker.']['global'];
				}
			}
			$markers['###is_error_' . $field . '###'] = $errorMessage;
		}
		if($this->settings['isErrorMarker.']['global']) {
			if($this->settings['isErrorMarker.']['global.']) {
				$errorMessage = $this->cObj->cObjGetSingle($this->settings['isErrorMarker.']['global'], $this->settings['isErrorMarker.']['global.']);
			} else {
				$errorMessage = $this->settings['isErrorMarker.']['global'];
			}
		} elseif (strlen(trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error'))) > 0) {
			$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':is_error'));
		}
		$markers['###is_error###'] = $errorMessage;

		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}

	/**
	 * Substitutes markers
	 * 		###error_[fieldname]###
	 * 		###ERROR###
	 * in $this->template
	 *
	 * @return void
	 */
	protected function fillErrorMarkers(&$errors) {
		$markers = array();
		$singleWrap = $this->settings['singleErrorTemplate.']['singleWrap'];
		foreach($errors as $field => $types) {
			$errorMessages = array();
			$clearErrorMessages = array();
			if(strlen(trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':error_' . $field))) > 0) {
				$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':error_' . $field));
				if($errorMessage) {
					if(strlen($singleWrap) > 0 && strstr($singleWrap, '|')) {
						$errorMessage = str_replace('|', $errorMessage, $singleWrap);
					}

					$errorMessages[] = $errorMessage;
				}
			}
			if(!is_array($types)) {
				$types = array($types);
			}
			foreach($types as $type) {

				$temp = t3lib_div::trimExplode(';', $type);
				$type = array_shift($temp);
				foreach($temp as $item) {
					$item = t3lib_div::trimExplode('::', $item);
					$values[$item[0]] = $item[1];
				}

					//try to load specific error message with key like error_fieldname_integer
				$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':error_' . $field . '_' . $type));
				if(strlen($errorMessage) == 0) {
					$type = strtolower($type);
					$errorMessage = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':error_' . $field . '_' . $type));
				}
				if($errorMessage) {
					if(is_array($values)) {
						foreach($values as $key => $value) {
							$errorMessage = str_replace('###' . $key . '###', $value, $errorMessage);
						}
					}
					if(strlen($singleWrap) > 0 && strstr($singleWrap,'|')) {
						$errorMessage = str_replace('|', $errorMessage, $singleWrap);
					}
					$errorMessages[] = $errorMessage;
				} else {
					Tx_Formhandler_StaticFuncs::debugMessage('no_error_message', 'error_' . $field . '_' . $type);
				}
			}
			$errorMessage = implode('', $errorMessages);
			$totalWrap = $this->settings['singleErrorTemplate.']['totalWrap'];
			if(strlen($totalWrap) > 0 && strstr($totalWrap, '|')) {
				$errorMessage = str_replace('|', $errorMessage, $totalWrap);
			}
			$clearErrorMessage = $errorMessage;
			if($this->settings['addErrorAnchors']) {
				$errorMessage = '<a name="' . $field . '">' . $errorMessage . '</a>';

			}
			$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($errorMessage, $this->langFile);
			$errorMessage = $this->cObj->substituteMarkerArray($errorMessage, $langMarkers);
			$markers['###error_' . $field . '###'] = $errorMessage;
			$markers['###ERROR_' . strtoupper($field) . '###'] = $errorMessage;
			$errorMessage = $clearErrorMessage;
			if($this->settings['addErrorAnchors']) {
				$errorMessage = '<a href="' . t3lib_div::getIndpEnv('REQUEST_URI') . '#' . $field . '">' . $errorMessage . '</a>';

			}
			//list settings
			$listSingleWrap = $this->settings['errorListTemplate.']['singleWrap'];
			if(strlen($listSingleWrap) > 0 && strstr($listSingleWrap, '|')) {
				$errorMessage = str_replace('|', $errorMessage, $listSingleWrap);
			}

			$markers['###ERROR###'] .= $errorMessage;
		}
		$totalWrap = $this->settings['errorListTemplate.']['totalWrap'];
		if(strlen($totalWrap) > 0 && strstr($totalWrap, '|')) {
			$markers['###ERROR###'] = str_replace('|', $markers['###ERROR###'], $totalWrap);
		}
		$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($markers['###ERROR###'], $this->langFile);
		$markers['###ERROR###'] = $this->cObj->substituteMarkerArray($markers['###ERROR###'], $langMarkers);
		$markers['###error###'] = $markers['###ERROR###'];
		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}

	/**
	 * Substitutes markers defined in TypoScript in $this->template
	 *
	 * @return void
	 */
	protected function fillTypoScriptMarkers() {
		$markers = array();
		foreach($this->settings['markers.'] as $name => $options) {
			if(!strstr($name, '.')) {
				if(!strcmp($options,'USER') || !strcmp($options,'USER_INT')) {
					$this->settings['markers.'][$name . '.']['gp'] = $this->gp;
				} 
				$markers['###' . $name . '###'] = $this->cObj->cObjGetSingle($this->settings['markers.'][$name], $this->settings['markers.'][$name . '.']);
			}
		}

		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);
	}

	/**
	 * Substitutes markers
	 * 		###value_[fieldname]###
	 * 		###VALUE_[FIELDNAME]###
	 * 		###[fieldname]###
	 * 		###[FIELDNAME]###
	 * in $this->template
	 *
	 * @return void
	 */
	protected function fillValueMarkers() {
		$markers = array();
		if (is_array($this->gp)) {
			foreach($this->gp as $k => $v) {
				if (!ereg('EMAIL_', $k)) {
					
					if (is_array($v)) {
						$v = implode(',', $v);
					}
					$v = trim($v);
					if ($v != '') {
						if(get_magic_quotes_gpc()) {
							$markers['###value_' . $k . '###'] = stripslashes(Tx_Formhandler_StaticFuncs::reverse_htmlspecialchars($v));
						} else {
							$markers['###value_' . $k . '###'] = Tx_Formhandler_StaticFuncs::reverse_htmlspecialchars($v);
						}
					} else {
						$markers['###value_' . $k . '###'] = '';
					}
				
					$markers['###' . $k . '###'] = $markers['###value_' . $k . '###'];
					$markers['###' . strtoupper($k) . '###'] = $markers['###value_' . $k . '###'];
					$markers['###' . (strtoupper('VALUE_' . $k)) . '###'] = $markers['###value_' . $k . '###'];
				} //if end
			} // foreach end
		} // if end
		
		$this->template = $this->cObj->substituteMarkerArray($this->template, $markers);

		//remove remaining VALUE_-markers
		//needed for nested markers like ###LLL:tx_myextension_table.field1.i.###value_field1###### to avoid wrong marker removal if field1 isn't set
		$this->template = preg_replace('/###value_.*?###/i', '', $this->template);
	}

	/**
	 * Substitutes markers
	 * 		###LLL:[languageKey]###
	 * in $this->template
	 *
	 * @return void
	 */
	protected function fillLangMarkers() {
		$langMarkers = array();
		if ($this->langFile != '') {
			$aLLMarkerList = array();
			preg_match_all('/###LLL:.+?###/Ssm', $this->template, $aLLMarkerList);
			foreach($aLLMarkerList[0] as $LLMarker){
				$llKey = substr($LLMarker, 7, (strlen($LLMarker) - 10));
				$marker = $llKey;
				$langMarkers['###LLL:' . $marker . '###'] = trim($GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . $llKey));
			}
		}
		$this->template = $this->cObj->substituteMarkerArray($this->template, $langMarkers);
	}
	
	/**
	 * improved copy from dam_index
	 * 
	 * Returns HTML of a box with a step counter and "back" and "next" buttons
	 * Use label "next"/"prev" or "next_[stepnumber]"/"prev_[stepnumber]" for specific step in language file as button text.
	 * 
	 * <code>
	 * #set background color
	 * plugin.Tx_Formhandler.settings.stepbar_color = #EAEAEA
	 * #use default CSS, written to temp file
	 * plugin.Tx_Formhandler.settings.useDefaultStepBarStyles = 1
	 * </code>
	 * 
	 * @author Johannes Feustel
	 * @param	integer	$currentStep current step (begins with 1)
	 * @param	integer	$lastStep last step
	 * @param	string	$buttonNameBack name attribute of the back button
	 * @param	string	$buttonNameFwd name attribute of the forward button
	 * @return 	string	HTML code
	 */
	protected function createStepBar($currentStep,$lastStep,$buttonNameBack ="",$buttonNameFwd ="") {

		//colors
		$bgcolor = '#EAEAEA';
		$bgcolor = $this->settings['stepbar_color'] ? $this->settings['stepbar_color'] : $bgcolor;

		$nrcolor = t3lib_div::modifyHTMLcolor($bgcolor, 30, 30, 30);

		$errorbgcolor = '#dd7777';
		$errornrcolor = t3lib_div::modifyHTMLcolor($errorbgcolor, 30, 30, 30);
		
		$classprefix = $this->settings['formValuesPrefix'] . '_stepbar';
		
		$css = array();
		$css[] = '.' . $classprefix . ' { background:'  . $bgcolor . '; padding:4px;}';
		$css[] = '.' . $classprefix . '_error { background: ' . $errorbgcolor . ';}';
		$css[] = '.' . $classprefix . '_steps { margin-left:50px; margin-right:25px; vertical-align:middle; font-family:Verdana,Arial,Helvetica; font-size:22px; font-weight:bold; }';
		$css[] = '.' . $classprefix . '_steps span { color:'.$nrcolor.'; margin-left:5px; margin-right:5px; }';
		$css[] = '.' . $classprefix . '_error .' . $classprefix . '_steps span { color:'.$errornrcolor.'; margin-left:5px; margin-right:5px; }';
		$css[] = '.' . $classprefix . '_steps .' . $classprefix . '_currentstep { color:  #000;}';
		$css[] = '#stepsFormButtons { margin-left:25px;vertical-align:middle;}';

		$content = '';
		$buttons = '';

		for ($i = 1; $i <= $lastStep; $i++) {
			$class = '';
			if ($i == $currentStep) {
				$class =  'class="' . $classprefix . '_currentstep"';
			}
			$content.= '<span ' . $class . ' >' . $i . '</span>';
		}
		$content = '<span class="' . $classprefix . '_steps' . '" style="">' . $content . '</span>';

		//if not the first step, show back button
		if ($currentStep > 1) {
			//check if label for specific step
			$buttonvalue = $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . 'prev_' . $currentStep) ? $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . "prev_" . $currentStep) : $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . 'prev');
			$buttons .= '<input type="submit" name="'.$buttonNameBack.'" value="' . trim($buttonvalue) . '" class="button_prev" style="margin-right:10px;" />';
		}

		$buttonvalue = $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . 'next_' . $currentStep) ? $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' . 'next_' . $currentStep) : $GLOBALS['TSFE']->sL('LLL:' . $this->langFile . ':' .'next');
		$buttons .= '<input type="submit" name="' . $buttonNameFwd . '" value="' . trim($buttonvalue) . '" class="button_next" />';

		$content .= '<span id="stepsFormButtons">' . $buttons . '</span>';
		
		//wrap
		$classes = $classprefix;
		if($this->errors) {
			$classes = $classes . ' ' . $classprefix . '_error';
		}
		$content = '<div class="' . $classes . '" >' . $content . '</div>';
		
		//add default css to page
		if($this->settings['useDefaultStepBarStyles']){
			$css = implode("\n", $css);
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . '_' . $classprefix] .= TSpagegen::inline2TempFile($css, 'css');
		}

		return $content;
	}
}
?>