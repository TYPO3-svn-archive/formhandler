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
 * Default controller for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */
class Tx_Formhandler_Controller_Form extends Tx_Formhandler_AbstractController {

	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
	 */
	protected $componentManager;

	/**
	 * The current GET/POST parameters of the form
	 *
	 * @access protected
	 * @var array
	 */
	protected $gp;

	/**
	 * Contains all errors occurred while validation
	 *
	 * @access protected
	 * @var array
	 */
	protected $errors;

	/**
	 * Holds the prefix value of all parameters of this form.
	 *
	 * @access protected
	 * @var string
	 */
	protected $formValuesPrefix;

	/**
	 * The global Formhandler configuration
	 *
	 * @access protected
	 * @var Tx_Formhandler_Configuration
	 */
	protected $configuration;

	/**
	 * The template file to be used. Only if template file was defined via plugin record
	 *
	 * @access protected
	 * @var string
	 */
	protected $templateFile;

	/**
	 * Array of configured translation files
	 *
	 * @access protected
	 * @var array
	 */
	protected $langFiles;

	/**
	 * The cObj
	 *
	 * @access protected
	 * @var tslib_cObj
	 */
	protected $cObj;

	/**
	 * Flag indicating if the form got submitted
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $submitted;

	/**
	 * The settings array
	 *
	 * @access protected
	 * @var array
	 */
	protected $settings;

	/**
	 * Flag indicating if debug mode is on
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $debugMode;

	/**
	 * The view object
	 *
	 * @access protected
	 * @var misc
	 */
	protected $view;

	/**
	 * The current step of the form
	 *
	 * @access protected
	 * @var integer
	 */
	protected $currentStep;

	/**
	 * The last step of the form
	 *
	 * @access protected
	 * @var integer
	 */
	protected $lastStep;

	/**
	 * Total steps of the form
	 *
	 * @access protected
	 * @var integer
	 */
	protected $totalSteps;

	/**
	 * Flag indicating if form is finished (no more steps)
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $finished;

	/**
	 * The constructor for a finisher setting the component manager and the configuration.
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->initializeController();
		$this->cObj = Tx_Formhandler_Globals::$cObj;
	}

	/**
	 * Main method of the form handler.
	 *
	 * @return rendered view
	 */
	public function process() {
		$this->init();
		$this->storeFileNamesInGP();
		$this->processFileRemoval();

		$action = t3lib_div::_GP('action');
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$temp = t3lib_div::_GP(Tx_Formhandler_Globals::$formValuesPrefix);
			$action = $temp['action'];
		}
		if ($action) {
			$content = $this->processAction($action);
			if(strlen(trim($content)) > 0) {
				return $content;
			}
		}

		if (!$this->submitted) {
			return $this->processNotSubmitted();
		} else {
			return $this->processSubmitted();
		}
	}

	protected function processAction($action) {
		$content = '';
		$gp = $_GET;
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$gp = t3lib_div::_GP(Tx_Formhandler_Globals::$formValuesPrefix);
		}
		if (is_array($this->settings['finishers.'])) {
			$finisherConf = array();
			foreach ($this->settings['finishers.'] as $key => $config) {
				if (strpos($key, '.') !== FALSE) {
					$className = Tx_Formhandler_StaticFuncs::prepareClassName($config['class']);
					if ($className === 'Tx_Formhandler_Finisher_SubmittedOK' && is_array($config['config.'])) {
						$finisherConf = $config['config.'];
					}
				}
			}
			$params = array();
			$tstamp = intval($gp['tstamp']);
			$hash = $GLOBALS['TYPO3_DB']->fullQuoteStr($gp['hash']);
			if ($tstamp && strpos($hash, ' ') === FALSE) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('params', 'tx_formhandler_log', 'tstamp=' . $tstamp . ' AND key_hash=' . $hash);
				if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) === 1) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
					$params = unserialize($row['params']);
				}
			}
			if ($finisherConf['actions.'][$action . '.'] && !empty($params)) {
				$class = $finisherConf['actions.'][$action . '.']['class'];
				if ($class) {
					$class = Tx_Formhandler_StaticFuncs::prepareClassName($class);
					$object = $this->componentManager->getComponent($class);
					$object->init($params, $finisherConf['actions.'][$action . '.']['config.']);
					$object->process();
				}
			} elseif($action === 'show') {
				
				//"show" makes it possible that Finisher_SubmittedOK show its output again
				$class = 'Tx_Formhandler_Finisher_SubmittedOK';
				$object = $this->componentManager->getComponent($class);
				unset($finisherConf['actions.']);
				$object->init($params, $finisherConf);
				$content = $object->process();
			}
		}
		return $content;
	}

	protected function processSubmitted() {

		/*
		 * Step may have been set to the next step already.
		 * Set the settings back to the one of the previous step 
		 * to run the right interceptors and validators.
		 */
		if ($this->currentStep > $this->lastStep) {
			$this->loadSettingsForStep($this->lastStep);
		} else {
			$this->loadSettingsForStep($this->currentStep);
		}
		
		$this->parseConditions();
		
		//read template file
		$this->templateFile = Tx_Formhandler_StaticFuncs::readTemplateFile($this->templateFile, $this->settings);
		Tx_Formhandler_Globals::$templateCode = $this->templateFile;
		$this->langFiles = Tx_Formhandler_StaticFuncs::readLanguageFiles($this->langFiles, $this->settings);
		Tx_Formhandler_Globals::$langFiles = $this->langFiles;
		
		$this->view->setLangFiles($this->langFiles);
		$this->view->setSettings($this->settings);
		$this->setViewSubpart($this->currentStep);

		//run init interceptors
		$this->addFormhandlerClass($this->settings['initInterceptors.'], 'Interceptor_Filtreatment');
		$output = $this->runClasses($this->settings['initInterceptors.']);
		if (strlen($output) > 0) {
			return $output;
		}

		Tx_Formhandler_Globals::$randomID = $this->gp['randomID'];
		
		$this->handleCheckBoxFields();

		//run validation
		$this->errors = array();
		$valid = array(TRUE);
		if (isset($this->settings['validators.']) && 
			is_array($this->settings['validators.']) && 
			intval($this->settings['validators.']['disable']) !== 1) {

			foreach ($this->settings['validators.'] as $idx => $tsConfig) {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class'])) {
					if (intval($tsConfig['disable']) !== 1) {
						$className = Tx_Formhandler_StaticFuncs::prepareClassName($tsConfig['class']);
						$validator = $this->componentManager->getComponent($className);
						if ($this->currentStep === $this->lastStep) {
							$userSetting = t3lib_div::trimExplode(',', $tsConfig['config.']['restrictErrorChecks']);
							$autoSetting = array(
								'fileAllowedTypes',
								'fileRequired',
								'fileMaxCount',
								'fileMinCount',
								'fileMaxSize',
								'fileMinSize'
							);
							$merged = array_merge($userSetting, $autoSetting);
							$tsConfig['config.']['restrictErrorChecks'] = implode(',', $merged);
						}
						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);
						$validator->init($this->gp, $tsConfig['config.']);
						$res = $validator->validate($this->errors);
						array_push($valid, $res);
					}
				} else {
					Tx_Formhandler_StaticFuncs::throwException('classesarray_error');
				}
			}
		}

		//if form is valid
		if ($this->isValid($valid)) {

			//process files
			$this->processFiles();

			//now set the settings to the current step again
			if ($this->currentStep > $this->lastStep) {
				$this->loadSettingsForStep($this->currentStep);
				$this->parseConditions();
				$this->view->setLangFiles($this->langFiles);
				$this->view->setSettings($this->settings);
				$this->setViewSubpart($this->currentStep);
			} else {
				$this->view->setLangFiles($this->langFiles);
				$this->view->setSettings($this->settings);
				$this->setViewSubpart($this->currentStep);
			}

			//if no more steps
			if ($this->finished) {
				return $this->processFinished();
			} else {

				$this->storeGPinSession();
				$this->mergeGPWithSession(FALSE, $this->currentStep);

				//display form
				return $this->view->render($this->gp, $this->errors);
			}
		} else {
			return $this->processNotValid();
		}
	}

	protected function processNotValid() {
		$this->gp['formErrors'] = $this->errors;
		Tx_Formhandler_Globals::$gp = $this->gp;

		//stay on current step
		if ($this->lastStep < Tx_Formhandler_Globals::$session->get('currentStep')) {
			Tx_Formhandler_Globals::$session->set('currentStep', $this->lastStep);
			$this->currentStep = $this->lastStep;
		}

		//load settings from last step again because an error occurred
		$this->loadSettingsForStep($this->currentStep);
		Tx_Formhandler_Globals::$session->set('settings', $this->settings);

		$this->view->setLangFiles($this->langFiles);
		$this->view->setSettings($this->settings);

		//reset the template because step had probably been decreased
		$this->setViewSubpart($this->currentStep);
		
		if ($this->currentStep >= $this->lastStep) {
			$this->storeGPinSession();
			$this->mergeGPWithSession(FALSE, $this->currentStep);
		}

		//display form
		return $this->view->render($this->gp, $this->errors);
	}

	protected function processFinished() {
		$this->storeGPinSession();
		$this->mergeGPWithSession();

		//run save interceptors
		$this->addFormhandlerClass($this->settings['saveInterceptors.'], 'Interceptor_Filtreatment');
		$output = $this->runClasses($this->settings['saveInterceptors.']);
		if (strlen($output) > 0) {
			return $output;
		}

		$this->storeGPinSession();
		$this->mergeGPWithSession(FALSE, $this->currentStep);

		//run loggers
		$this->addFormhandlerClass($this->settings['loggers.'], 'Logger_DB');
		$output = $this->runClasses($this->settings['loggers.']);
		if (strlen($output) > 0) {
			return $output;
		}

		//run finishers
		if (isset($this->settings['finishers.']) && is_array($this->settings['finishers.']) && intval($this->settings['finishers.']['disable']) !== 1) {
			ksort($this->settings['finishers.']);

			//if storeGP is set include Finisher_storeGP, stores GET / POST in the session
			if (Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], 'store_gp', 'sMISC')){
				$this->addFormhandlerClass($this->settings['finishers.'], 'Finisher_StoreGP');
			}

			foreach ($this->settings['finishers.'] as $idx => $tsConfig) {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class'])) {
					if (intval($tsConfig['disable']) !== 1) {
						$className = Tx_Formhandler_StaticFuncs::prepareClassName($tsConfig['class']);
						$finisher = $this->componentManager->getComponent($className);

						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);

						$finisher->init($this->gp, $tsConfig['config.']);
						$this->storeGPinSession();
						$this->mergeGPWithSession(FALSE, $this->currentStep);

						//if the finisher returns HTML (e.g. Tx_Formhandler_Finisher_SubmittedOK)
						if ($tsConfig['config.']['returns']) {
							Tx_Formhandler_Globals::$session->set('finished', TRUE);
							return $finisher->process();
						} else {
							$this->gp = $finisher->process();
							Tx_Formhandler_Globals::$gp = $this->gp;
						}
					}
				} else {
					Tx_Formhandler_StaticFuncs::throwException('classesarray_error');
				}
			}
			Tx_Formhandler_Globals::$session->set('finished', TRUE);
		}
	}

	protected function processNotSubmitted() {
		$this->loadSettingsForStep($this->currentStep);
		$this->parseConditions();
		
		$this->view->setSettings($this->settings);
		
		//read template file
		$this->templateFile = Tx_Formhandler_StaticFuncs::readTemplateFile($this->templateFile, $this->settings);
		Tx_Formhandler_Globals::$templateCode = $this->templateFile;
		$this->langFiles = Tx_Formhandler_StaticFuncs::readLanguageFiles($this->langFiles, $this->settings);
		Tx_Formhandler_Globals::$langFiles = $this->langFiles;
		
		$this->view->setLangFiles($this->langFiles);
		$this->setViewSubpart($this->currentStep);

		//run preProcessors
		$output = $this->runClasses($this->settings['preProcessors.']);
		if (strlen($output) > 0) {
			return $output;
		}

		//run init interceptors
		$this->addFormhandlerClass($this->settings['initInterceptors.'], 'Interceptor_Filtreatment');
		$output = $this->runClasses($this->settings['initInterceptors.']);
		if (strlen($output) > 0) {
			return $output;
		}

		//display form
		$content = $this->view->render($this->gp, $this->errors);
		return $content;
	}

	protected function storeFileNamesInGP() {

		//put file names into $this->gp
		$sessionFiles = Tx_Formhandler_Globals::$session->get('files');
		if (!is_array($sessionFiles)) {
			$sessionFiles = array();
		}
		foreach ($sessionFiles as $fieldname => $files) {
			$fileNames = array();
			if (is_array($files)) {
				foreach ($files as $idx => $fileInfo) {
					$fileName = $fileInfo['uploaded_name'];
					if (!$fileName) {
						$fileName = $fileInfo['name'];
					}
					$fileNames[] = $fileName;
				}
			}
			$this->gp[$fieldname] = implode(',', $fileNames);
		}
	}

	protected function addDefaultComponentConfig($conf) {
		if (!$conf['langFiles']) {
			$conf['langFiles'] = $this->langFiles;
		}
		$conf['formValuesPrefix'] = $this->settings['formValuesPrefix'];
		$conf['templateSuffix'] = $this->settings['templateSuffix'];
		return $conf;
	}

	/**
	 * Adds a mandatory component to the classes array
	 *
	 * @return void
	 */
	protected function addFormhandlerClass(&$classesArray, $className){

		if (!isset($classesArray) && !is_array($classesArray)) {

			//add class to the end of the array
			$classesArray[] = array('class' => $className);
		} else {
			$found = FALSE;
			foreach ($classesArray as $idx => $classOptions) {
				if (strpos($className, $classOptions['class']) !== FALSE) {
					$found = TRUE;
				} elseif (strpos(str_replace('Tx_Formhandler_', '', $className), $classOptions['class']) !== FALSE) {
					$found = TRUE;
				}
			}
			if (!$found) {

				//add class to the end of the array
				$classesArray[] = array('class' => $className);
			}
		}
	}

	protected function processFileRemoval() {
		if ($this->gp['removeFile']) {
			$filename = $this->gp['removeFile'];
			$fieldname = $this->gp['removeFileField'];
			$sessionFiles = Tx_Formhandler_Globals::$session->get('files');
			if (is_array($sessionFiles)) {
				foreach ($sessionFiles as $field => $files) {
					if (!strcmp($field, $fieldname)) {
						$found = FALSE;
						foreach ($files as $key => $fileInfo) {
							if (!strcmp($fileInfo['uploaded_name'], $filename)) {
								$found = TRUE;
								unset($sessionFiles[$field][$key]);
							}
						}
						if (!$found) {
							foreach ($files as $key => $fileInfo) {
								if (!strcmp($fileInfo['name'], $filename)) {
									unset($sessionFiles[$field][$key]);
								}
							}
						}
					}
				}
			}
			unset($this->gp['removeFile']);
			unset($this->gp['removeFileField']);
			Tx_Formhandler_Globals::$session->set('files', $sessionFiles);
		}
	}

	/**
	 * Processes uploaded files, moves them to a temporary upload folder, renames them if they already exist and
	 * stores the information in user session
	 *
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function processFiles() {
		$sessionFiles = Tx_Formhandler_Globals::$session->get('files');
		$tempFiles = $sessionFiles;

		//if files were uploaded
		if (isset($_FILES) && is_array($_FILES) && !empty($_FILES)) {

			//get upload folder
			$uploadFolder = Tx_Formhandler_StaticFuncs::getTempUploadFolder();

			//build absolute path to upload folder
			$uploadPath = Tx_Formhandler_StaticFuncs::getTYPO3Root() . $uploadFolder;

			if (!file_exists($uploadPath)) {
				Tx_Formhandler_StaticFuncs::debugMessage('folder_doesnt_exist', array($uploadPath), 3);
				return;
			}

			//for all file properties
			foreach ($_FILES as $sthg => $files) {

				//if a file was uploaded
				if (isset($files['name']) && is_array($files['name'])) {

					//for all file names
					foreach ($files['name'] as $field => $name) {
						if (!isset($this->errors[$field])) {
							$exists = FALSE;
							if (is_array($sessionFiles[$field])) {
								foreach ($sessionFiles[$field] as $idx => $fileOptions) {
									if ($fileOptions['name'] === $name) {
										$exists = TRUE;
									}
								}
							}
							if (!$exists) {
								$filename = substr($name, 0, strpos($name, '.'));
								if (strlen($filename) > 0) {
									$ext = substr($name, strpos($name, '.'));
									$suffix = 1;
									$filename = str_replace(' ', '_', $filename);

									//build file name
									$uploadedFileName = $filename . $ext;

									//rename if exists
									while(file_exists($uploadPath . $uploadedFileName)) {
										$uploadedFileName = $filename . '_' . $suffix . $ext;
										$suffix++;
									}
									$files['name'][$field] = $uploadedFileName;

									//move from temp folder to temp upload folder
									move_uploaded_file($files['tmp_name'][$field], $uploadPath . $uploadedFileName);
									t3lib_div::fixPermissions($uploadPath . $uploadedFileName);
									$files['uploaded_name'][$field] = $uploadedFileName;

									//set values for session
									$tmp['name'] = $name;
									$tmp['uploaded_name'] = $uploadedFileName;
									$tmp['uploaded_path'] = $uploadPath;
									$tmp['uploaded_folder'] = $uploadFolder;
									$uploadedUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $uploadFolder . $uploadedFileName;
									$uploadedUrl = str_replace('//', '/', $uploadedUrl);
									$tmp['uploaded_url'] = $uploadedUrl;
									$tmp['size'] = $files['size'][$field];
									$tmp['type'] = $files['type'][$field];
									if (!is_array($tempFiles[$field]) && strlen($field) > 0) {
										$tempFiles[$field] = array();
									}
									array_push($tempFiles[$field], $tmp);
									if (!is_array($this->gp[$field])) {
										$this->gp[$field] = array();
									}
									array_push($this->gp[$field], $uploadedFileName);
								}
							}
						}
					}
				}
			}
		}

		Tx_Formhandler_Globals::$session->set('files', $tempFiles);
		Tx_Formhandler_StaticFuncs::debugMessage('Files:', array(), 1, (array)$tempFiles);
	}

	/**
	 * Stores the current GET/POST parameters in SESSION
	 *
	 * @param array &$settings Reference to the settings array to get information about checkboxes and radiobuttons.
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function storeGPinSession() {

		$newGP = Tx_Formhandler_StaticFuncs::getMergedGP();
		$data = Tx_Formhandler_Globals::$session->get('values');

		//set the variables in session
		if ($this->lastStep !== $this->currentStep) {
			foreach ($newGP as $key => $value) {
				if (!strstr($key, 'step-') && $key !== 'submitted' && $key !== 'randomID') {
					$data[$this->lastStep][$key] = $this->gp[$key];
				}
			}
		}

		Tx_Formhandler_Globals::$session->set('values', $data);
	}

	protected function reset() {
		$values = array (
			'values' => NULL,
			'files' => NULL,
			'lastStep' => NULL,
			'currentStep' => 1,
			'startblock' => NULL,
			'endblock' => NULL,
			'inserted_uid' => NULL,
			'inserted_tstamp' => NULL,
			'key_hash' => NULL,
			'finished' => NULL
		);
		Tx_Formhandler_Globals::$session->setMultiple($values);
		$this->gp = array();
		$this->currentStep = 1;
		Tx_Formhandler_Globals::$gp = $this->gp;
		Tx_Formhandler_StaticFuncs::debugMessage('cleared_session');
	}

	/**
	 * Searches for current step and sets $this->currentStep according
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function findCurrentStep() {
		if (isset($this->gp) && is_array($this->gp)) {
			$action = 'reload';
			$keys = array_keys($this->gp);
			foreach ($keys as $idx => $pname) {

				if (strstr($pname, 'step-')) {
					preg_match_all('/step-([0-9]+)-([a-z]+)/', $pname, $matches);
					if (isset($matches[2][0])) {
						$action = $matches[2][0];
						$step = intval($matches[1][0]);
					}
				}
			}
		}

		switch ($action) {
			case 'next':
				if ($step !== intval(Tx_Formhandler_Globals::$session->get('currentStep'))) {
					$this->currentStep = intval(Tx_Formhandler_Globals::$session->get('currentStep')) + 1;
				} else {
					$this->currentStep = $step;
				}
				break;
			case 'prev':
				if ($step !== intval(Tx_Formhandler_Globals::$session->get('currentStep'))) {
					$this->currentStep = intval(Tx_Formhandler_Globals::$session->get('currentStep')) - 1;
				} else {
					$this->currentStep = $step;
				}
				if ($this->currentStep < 1) {
					$this->currentStep = 1;
				}
				break;
			default:
				$this->currentStep = intval(Tx_Formhandler_Globals::$session->get('currentStep'));
				break;
		}
		if (!$this->currentStep) {
			$this->currentStep = 1;
		}
		Tx_Formhandler_StaticFuncs::debugMessage('current_step', array($this->currentStep));
	}

	public function validateConfig() {
		$options = array(
			array('to_email', 'sEMAILADMIN', 'finishers', 'Tx_Formhandler_Finisher_Mail'),
			array('to_email', 'sEMAILUSER', 'finishers', 'Tx_Formhandler_Finisher_Mail'),
			array('redirect_page', 'sMISC', 'finishers', 'Tx_Formhandler_Finisher_Redirect'),
			array('required_fields', 'sMISC', 'validators', 'Tx_Formhandler_Validator_Default'),
		);
		foreach ($options as $idx => $option) {
			$fieldName = $option[0];
			$flexformSection = $option[1];
			$component = $option[2];
			$componentName = $option[3];
			$value = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], $fieldName, $flexformSection);

			// Check if a Mail Finisher can be found in the config
			$isConfigOk = FALSE;
			if (is_array($this->settings[$component . '.'])) {
				foreach ($this->settings[$component . '.'] as $idx => $finisher) {
					if ($finisher['class'] == $componentName
						|| @is_subclass_of($finisher['class'], $componentName)) {

						$isConfigOk = TRUE;
						break;
					} elseif (	$finisher['class'] == (str_replace('Tx_Formhandler_', '', $componentName))
								|| @is_subclass_of('Tx_Formhandler_' . $finisher['class'], $componentName)) {

						$isConfigOk = TRUE;
						break;
					}
				}
			}

			// Throws an Exception if a problem occurs
			if ($value != '' && !$isConfigOk) {
				Tx_Formhandler_StaticFuncs::throwException('missing_component', $component, $value, $componentName);
			}
		}
	}

	protected function parseConditionsBlock($settings) {
		$finalResult = FALSE;
		foreach ($settings['if.'] as $idx => $conditionSettings) {
			$conditions = $conditionSettings['conditions.'];
			$condition = '';
			$orConditions = array();
			foreach ($conditions as $subIdx => $andConditions) {
				$results = array();
				foreach ($andConditions as $subSubIdx => $andCondition) {
					if (strstr($andCondition, '=')) {
						list($field, $value) = t3lib_div::trimExplode('=', $andCondition);
						$result = (Tx_Formhandler_Globals::$cObj->getGlobal($field, $this->gp) === $value);
					} elseif (strstr($andCondition, '>')) {
						list($field, $value) = t3lib_div::trimExplode('>', $andCondition);
						$result = (Tx_Formhandler_Globals::$cObj->getGlobal($field, $this->gp) > $value);
					} elseif (strstr($andCondition, '<')) {
						list($field, $value) = t3lib_div::trimExplode('<', $andCondition);
						$result = (Tx_Formhandler_Globals::$cObj->getGlobal($field, $this->gp) < $value);
					} elseif (strstr($andCondition, '!=')) {
						list($field, $value) = t3lib_div::trimExplode('!=', $andCondition);
						$result = (Tx_Formhandler_Globals::$cObj->getGlobal($field, $this->gp) !== $value);
					} else {
						$field = $andCondition;
						$keys = explode('|', $field);
						$numberOfLevels = count($keys);
						$rootKey = trim($keys[0]);
						$value = $this->gp[$rootKey];

						$result = isset($this->gp[$rootKey]);
						for ($i = 1; $i < $numberOfLevels && isset($value); $i++) {
							$currentKey = trim($keys[$i]);
							if (is_object($value)) {
								$value = $value->$currentKey;
								$result = isset($value->$currentKey);
							} elseif (is_array($value)) {
								$value = $value[$currentKey];
								$result = isset($value[$currentKey]);
							} else {
								$result = FALSE;
							}
						}
					}
					
					$results[] = ($result ? 'TRUE' : 'FALSE');
				}
				$orConditions[] = '(' . implode(' && ', $results) . ')';
			}
			$finalCondition = '(' . implode(' || ', $orConditions) . ')';

			eval('$evaluation = ' . $finalCondition . ';');

			if ($evaluation) {
				$newSettings = $conditionSettings['isTrue.'];
				if (is_array($newSettings)) {
					$this->settings = t3lib_div::array_merge_recursive_overrule($this->settings, $newSettings);
				}
			} else {
				$newSettings = $conditionSettings['else.'];
				if (is_array($newSettings)) {
					$this->settings = t3lib_div::array_merge_recursive_overrule($this->settings, $newSettings);
				}
			}
		
		}
	}

	protected function parseConditions() {

		//parse global conditions
		if (is_array($this->settings['if.'])) {
			$this->parseConditionsBlock($this->settings);
		}

		//parse conditions for each of the previous steps
		$endStep = Tx_Formhandler_Globals::$session->get('currentStep');
		$step = 1;

		while($step <= $endStep) {
			$stepSettings = $this->settings[$step . '.'];
			if (is_array($stepSettings['if.'])) {
				$this->parseConditionsBlock($stepSettings);
			}
			$step++;
		}
	}

	protected function init() {

		$this->settings = $this->getSettings();
		$this->formValuesPrefix = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'formValuesPrefix');
		Tx_Formhandler_Globals::$formID = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'formID');
		Tx_Formhandler_Globals::$formValuesPrefix = $this->formValuesPrefix;

		//set debug mode
		$this->debugMode = (intval($this->settings['debug']) === 1);

		$sessionClass = 'Tx_Formhandler_Session_PHP';
		if($this->settings['session.']) {
			$sessionClass = Tx_Formhandler_StaticFuncs::prepareClassName($this->settings['session.']['class']);
		}

		Tx_Formhandler_Globals::$session = $this->componentManager->getComponent($sessionClass);
		$this->gp = Tx_Formhandler_Staticfuncs::getMergedGP();

		$randomID = $this->gp['randomID'];
		if (!$randomID) {
			$randomID = Tx_Formhandler_StaticFuncs::generateRandomID();
		}
		Tx_Formhandler_Globals::$randomID = $randomID;
		
		$action = t3lib_div::_GP('action');
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$temp = t3lib_div::_GP(Tx_Formhandler_Globals::$formValuesPrefix);
			$action = $temp['action'];
		}
		if(Tx_Formhandler_Globals::$session->get('finished') && !$action) {
			Tx_Formhandler_Globals::$session->reset();
			unset($_GET[Tx_Formhandler_Globals::$formValuesPrefix]);
			unset($_GET['id']);
			Tx_Formhandler_StaticFuncs::doRedirect($GLOBALS['TSFE']->id, FALSE, $_GET);
			exit();
		}
		$this->parseConditions();
		$this->getStepInformation();

		$currentStepFromSession = Tx_Formhandler_Globals::$session->get('currentStep');
		$prevStep = $currentStepFromSession;
		if ($this->settings['prevStep']) {
			$prevStep = $this->settings['prevStep'];
		}
		if (intval($prevStep) !== intval($currentStepFromSession)) {
			$this->currentStep = 1;
			$this->lastStep = 1;
			Tx_Formhandler_StaticFuncs::throwException('You messed with the steps!');
		}

		$this->mergeGPWithSession(FALSE, $this->currentStep);

		$this->parseConditions();

		//read template file
		$this->templateFile = Tx_Formhandler_StaticFuncs::readTemplateFile($this->templateFile, $this->settings);
		Tx_Formhandler_Globals::$templateCode = $this->templateFile;
		$this->langFiles = Tx_Formhandler_StaticFuncs::readLanguageFiles($this->langFiles, $this->settings);
		Tx_Formhandler_Globals::$langFiles = $this->langFiles;

		$this->validateConfig();
		Tx_Formhandler_Globals::$settings = $this->settings;

		//set debug mode again cause it may have changed in specific step settings
		$this->debugMode = (intval($this->settings['debug']) === 1);
		Tx_Formhandler_Globals::$session->set('debug', $this->debugMode);
		
		if (!is_array($this->settings['debuggers.'])) {
			$this->settings['debuggers.'] = array(
				'1.' => array(
					'class' => 'Tx_Formhandler_Debugger_Print'
				)
			);
		}
		
		foreach ($this->settings['debuggers.'] as $idx => $options) {
			if(intval($options['disable']) !== 1) {
				$debuggerClass = $options['class'];
				$debuggerClass = Tx_Formhandler_StaticFuncs::prepareClassName($debuggerClass);
				$debugger = $this->componentManager->getComponent($debuggerClass);
				$debugger->init($this->gp, $options['config.']);
				$debugger->validateConfig();
				Tx_Formhandler_Globals::$debuggers[] = $debugger;
			}
		}

		Tx_Formhandler_StaticFuncs::debugMessage('using_prefix', array($this->formValuesPrefix));

		//init view
		$viewClass = $this->settings['view'];
		if (!$viewClass) {
			$viewClass = 'Tx_Formhandler_View_Form';
		}
		
		Tx_Formhandler_StaticFuncs::debugMessage('using_view', array($viewClass));
		Tx_Formhandler_StaticFuncs::debugMessage('current_gp', array(), 1, $this->gp);

		$this->storeSettingsInSession();

		$this->mergeGPWithSession(FALSE, $this->currentStep);

		//set submitted
		$this->submitted = $this->isFormSubmitted();

		if (!$this->submitted) {
			$this->reset();
		}

		// set stylesheet file(s)
		$this->addCSS();

		// add JavaScript file(s)
		$this->addJS();

		Tx_Formhandler_StaticFuncs::debugMessage('current_session_params', array(), 1, (array)Tx_Formhandler_Globals::$session->get('values'));

		$viewClass = Tx_Formhandler_StaticFuncs::prepareClassName($viewClass);
		$this->view = $this->componentManager->getComponent($viewClass);
		$this->view->setLangFiles($this->langFiles);
		$this->view->setSettings($this->settings);
		$this->setViewSubpart($this->currentStep);

		Tx_Formhandler_Globals::$gp = $this->gp;

		//init ajax
		if ($this->settings['ajax.']) {
			$class = $this->settings['ajax.']['class'];
			if (!$class) {
				$class = 'Tx_Formhandler_AjaxHandler_JQuery';
			}
			Tx_Formhandler_StaticFuncs::debugMessage('using_ajax', array($class));
			$class = Tx_Formhandler_StaticFuncs::prepareClassName($class);
			$ajaxHandler = $this->componentManager->getComponent($class);
			Tx_Formhandler_Globals::$ajaxHandler = $ajaxHandler;

			$ajaxHandler->init($this->settings['ajax.']['config.']);
			$ajaxHandler->initAjax();
		}
		if (!$this->gp['randomID']) {
			$this->gp['randomID'] = Tx_Formhandler_Globals::$randomID;
		}
	}

	protected function isFormSubmitted() {
		$submitted = $this->gp['submitted'];
		if ($submitted) {
			foreach ($this->gp as $key => $value) {
				if (substr($key, 0, 5) === 'step-') {
					$submitted = TRUE;
				}
			}
		} elseif (intval($this->settings['skipView']) === 1) {
			$submitted = TRUE;
		}
		
		return $submitted;
	}

	/**
	 * Sets the template of the view.
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function setViewSubpart($step) {
		$this->finished = FALSE;

		if (intval($this->settings['skipView']) === 1) {
			$this->finished = TRUE;
		} elseif (strstr($this->templateFile, ('###TEMPLATE_FORM' . $step . $this->settings['templateSuffix'] . '###'))) {
			
			// search for ###TEMPLATE_FORM[step][suffix]###
			Tx_Formhandler_StaticFuncs::debugMessage('using_subpart', array('###TEMPLATE_FORM' . $step . $this->settings['templateSuffix'] . '###'));
			$this->view->setTemplate($this->templateFile, ('FORM' . $step . $this->settings['templateSuffix']));
		} elseif (!isset($this->settings['templateSuffix']) && strstr($this->templateFile, ('###TEMPLATE_FORM' . $step . '###'))) {
			
			//search for ###TEMPLATE_FORM[step]###
			Tx_Formhandler_StaticFuncs::debugMessage('using_subpart', array('###TEMPLATE_FORM' . $step . '###'));
			$this->view->setTemplate($this->templateFile, ('FORM' . $step));

		} elseif (intval($step) === intval(Tx_Formhandler_Globals::$session->get('lastStep')) + 1) {
			$this->finished = TRUE;
		}
	}

	protected function storeSettingsInSession() {
		$values = array (
			'formValuesPrefix' => $this->formValuesPrefix,
			'settings' => $this->settings,
			'debug' => $this->debugMode,
			'currentStep' => $this->currentStep,
			'totalSteps' => $this->totalSteps,
			'lastStep' => $this->lastStep,
			'templateSuffix' => $this->settings['templateSuffix']
		);
		Tx_Formhandler_Globals::$session->setMultiple($values);
		
		Tx_Formhandler_Globals::$formValuesPrefix = $this->formValuesPrefix;
		Tx_Formhandler_Globals::$templateSuffix = $this->settings['templateSuffix'];
	}

	protected function loadSettingsForStep($step) {
		//merge settings with specific settings for current step
		if (isset($this->settings[$step . '.']) && is_array($this->settings[$step . '.'])) {
			$this->settings = array_merge($this->settings, $this->settings[$step . '.']);
		}
		Tx_Formhandler_Globals::$session->set('settings', $this->settings);
	}

	protected function getStepInformation() {

		//find current step
		$this->findCurrentStep();

		//set last step
		$this->lastStep = Tx_Formhandler_Globals::$session->get('currentStep');
		if (!$this->lastStep) {
			$this->lastStep = 1;
		}

		//total steps
		preg_match_all('/(###TEMPLATE_FORM)([0-9]+)(_.*)?(###)/', $this->templateFile, $subparts);

		//get step numbers
		$subparts = array_unique($subparts[2]);
		sort($subparts);
		$countSubparts = count($subparts);
		$this->totalSteps = $subparts[$countSubparts - 1];
		if ($this->totalSteps > $countSubparts) {
			Tx_Formhandler_StaticFuncs::debugMessage('subparts_missing', array(implode(', ', $subparts)), 2);
		} else {
			Tx_Formhandler_StaticFuncs::debugMessage('total_steps', array($this->totalSteps));
		}
	}

	/**
	 * Merges the current GET/POST parameters with the stored ones in SESSION
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function mergeGPWithSession($overruleGP = TRUE, $maxStep = 0) {
		if (!is_array($this->gp)) {
			$this->gp = array();
		}
		$values = Tx_Formhandler_Globals::$session->get('values');
		if (!is_array($values)) {
			$values = array();
		}

		foreach ($values as $step => &$params) {
			if (is_array($params) && (!$maxStep || $step <= $maxStep)) {
				unset($params['submitted']);
				foreach ($params as $key => $value) {
					if ($overruleGP || !isset($this->gp[$key])) {
						$this->gp[$key] = $value;
					}
				}
			}
		}
	}

	/**
	 * Runs the class by calling process() method.
	 *
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 * @param array $classesArray: the configuration array
	 * @return void
	 */
	protected function runClasses($classesArray) {
		$output = '';
		if (isset($classesArray) && is_array($classesArray) && intval($classesArray['disable']) !== 1) {

			foreach ($classesArray as $idx => $tsConfig) {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class'])) {
					if (intval($tsConfig['disable']) !== 1) {
						$className = Tx_Formhandler_StaticFuncs::prepareClassName($tsConfig['class']);
						Tx_Formhandler_StaticFuncs::debugMessage('calling_class', array($className));
						$obj = $this->componentManager->getComponent($className);
						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);
						$obj->init($this->gp, $tsConfig['config.']);
						$return = $obj->process();
						if (is_array($return)) {

							//return value is an array. Treat it as the probably modified get/post parameters
							$this->gp = $return;
							Tx_Formhandler_Globals::$gp = $this->gp;
						} else {

							//return value is no array. treat this return value as output.
							return $return;
						}
					}
				} else {
					Tx_Formhandler_StaticFuncs::throwException('classesarray_error');
				}
			}
		}
	}

	/**
	 * Read stylesheet file(s) set in TypoScript. If set add to header data
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function addCSS() {
		$stylesheetFile = $this->settings['cssFile'];
		$cssFiles = array();
		if ($this->settings['cssFile.']) {
			foreach ($this->settings['cssFile.'] as $idx => $file) {
				$cssFiles[] = $file;
			}
		} elseif (strlen($stylesheetFile) > 0) {
			$cssFiles[] = $stylesheetFile;
		}
		foreach ($cssFiles as $idx => $file) {

			// set stylesheet
			$GLOBALS['TSFE']->additionalHeaderData[$this->configuration->getPackageKeyLowercase()] .=
				'<link rel="stylesheet" href="' . Tx_Formhandler_StaticFuncs::resolveRelPathFromSiteRoot($file) . '" type="text/css" media="screen" />' . "\n";
		}
	}

	/**
	 * Read JavaScript file(s) set in TypoScript. If set add to header data
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function addJS() {
		$jsFile = $this->settings['jsFile'];
		$jsFiles = array();
		if ($this->settings['jsFile.']) {
			foreach ($this->settings['jsFile.'] as $idx => $file) {
				$jsFiles[] = $file;
			}
		} elseif (strlen($jsFile) > 0) {
			$jsFiles[] = $jsFile;
		}
		foreach ($jsFiles as $idx => $file) {

			// set stylesheet
			$GLOBALS['TSFE']->additionalHeaderData[$this->configuration->getPackageKeyLowercase()] .=
				'<script type="text/javascript" src="' . Tx_Formhandler_StaticFuncs::resolveRelPathFromSiteRoot($file) . '"></script>' . "\n";
		}
	}

	/**
	 * Find out if submitted form was valid. If one of the values in the given array $valid is FALSE the submission was not valid.
	 *
	 * @param $validArr Array with the return values of each validator
	 * @return boolean
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function isValid($validArr) {
		$valid = TRUE;
		if (is_array($validArr)) {
			foreach ($validArr as $idx => $item) {
				if (!$item) {
					$valid = FALSE;
				}
			}
		}
		return $valid;
	}
	
	protected function handleCheckBoxFields() {
		
		$newGP = Tx_Formhandler_StaticFuncs::getMergedGP();
		
		//check for checkbox fields using the values in $newGP
		if ($this->settings['checkBoxFields']) {
			$fields = t3lib_div::trimExplode(',', $this->settings['checkBoxFields']);
			foreach ($fields as $idx => $field) {
				if (!isset($newGP[$field]) && isset($this->gp[$field])) {
					if($this->lastStep < $this->currentStep) {
						$this->gp[$field] = array();
					}
				}
			}
		}
	}

	/**
	 * Possibly unnecessary
	 *
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function initializeController($value = '') {

	}

}
?>