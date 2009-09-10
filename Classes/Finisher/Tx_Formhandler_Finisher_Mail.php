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
 * Finisher to send mails after successful form submission.
 *
 * A sample configuration looks like this:
 *
 * <code>
 * finishers.2.class = Tx_Formhandler_Finisher_Mail
 * finishers.2.config.limitMailsToUser = 5
 * finishers.2.config.checkBinaryCfLr = firstname,text,email
 * finishers.2.config.admin.header =
 * finishers.2.config.admin.to_email = rf@typoheads.at
 * finishers.2.config.admin.to_name = Reinhard Führicht
 * finishers.2.config.admin.subject = SingleStep Request
 * finishers.2.config.admin.sender_email = email
 * finishers.2.config.admin.sender_name = lastname
 * finishers.2.config.admin.replyto_email = email
 * finishers.2.config.admin.replyto_name = lastname
 * finishers.2.config.admin.cc_email = office@host.com
 * finishers.2.config.admin.htmlEmailAsAttachment = 1
 * finishers.2.config.user.header = ...
 * finishers.2.config.user.to_email = email
 * finishers.2.config.user.to_name = lastname
 * finishers.2.config.user.subject = Your SingleStep request
 * finishers.2.config.user.sender_email = rf@typoheads.at
 * finishers.2.config.user.sender_name = Reinhard Führicht
 * finishers.2.config.user.replyto_email = rf@typoheads.at
 * finishers.2.config.user.replyto_name = TEXT
 * finishers.2.config.user.replyto_name.value = Reinhard Führicht
 * finishers.2.config.user.cc_email = controlling@host.com
 * finishers.2.config.user.cc_name = Contact Request
 *
 * # sends only plain text mails and adds the HTML mail as attachment
 * finishers.2.config.user.htmlEmailAsAttachment = 1
 *
 * # attaches static files or files uploaded via a form field
 * finishers.2.config.user.attachment = fileadmin/files/file.txt,picture
 *
 * # attaches a PDF file with submitted values
 * finishers.2.config.user.attachPDF.class = Tx_Formhandler_Generator_PDF
 * finishers.2.config.user.attachPDF.exportFields = firstname,lastname,email,interests,pid,submission_date,ip
 * 
 * #configure how the attached files are prefixes (PDF/HTML).
 * # both files prefixed equally:
 * finishers.2.config.user.filePrefix = MyContactForm_
 * 
 * # different prefixes for the files.
 * finishers.2.config.html = MyContactForm_
 * finishers.2.config.pdf = MyContactFormPDF_
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_Mail extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		$this->init();

		//send emails
		$this->sendMail('admin');
		$this->sendMail('user');
		
		return $this->gp;
	}

	/**
	 * Returns the final template code for given mode and suffix with substituted markers.
	 *
	 * @param string $mode user/admin
	 * @param string $suffix plain/html
	 * @return string The template code
	 */
	protected function parseTemplate($mode, $suffix) {
		$view = $this->componentManager->getComponent('Tx_Formhandler_View_Mail');
		$view->setLangFile($this->langFile);
		$view->setPredefined($this->predefined);
		
		$templateCode = Tx_Formhandler_StaticFuncs::readTemplateFile($this->settings['templateFile'], $this->settings);
		$view->setTemplate($templateCode, ('EMAIL_' . strtoupper($mode) . '_' . strtoupper($suffix) . $this->settings['templateSuffix']));
		if(!$view->hasTemplate()) {
			$view->setTemplate($templateCode, ('EMAIL_' . strtoupper($mode) . '_' . strtoupper($suffix)));
			if(!$view->hasTemplate()) {
				Tx_Formhandler_StaticFuncs::debugMessage('no_mail_template', $mode, $suffix);
			}
		}
		
		return $view->render($this->gp, array('mode' => $suffix));
	}

	/**
	 * Sanitizes E-mail markers by processing the 'checkBinaryCrLf' setting in TypoScript
	 *
	 * @param array &$markers The E-mail markers
	 * @return void
	 */
	protected function sanitizeMarkers(&$markers) {
		$checkBinaryCrLf = $this->settings['checkBinaryCrLf'];
		if ($checkBinaryCrLf != '') {
			$markersToCheck = t3lib_div::trimExplode(',', $checkBinaryCrLf);
			foreach($markersToCheck as $idx => $val) {
				if(substr($val,0,3) != '###') {
					$val = '###' . $markersToCheck[$idx];
				}

				if(substr($val,-3) != '###') {
					$val .= '###';
				}
				$iStr = $markers[$val];
				$iStr = str_replace (chr(13), '<br />', $iStr);
				$iStr = str_replace ('\\', '', $iStr);
				$markers[$val] = $iStr;

			}
		}
		foreach($markers as $field => &$value) {
			$value = nl2br($value);
		}
	}

	/**
	 * Sends mail according to given type.
	 *
	 * @param string $type (admin|user)
	 * @return void
	 */
	protected function sendMail($type) {
		$doSend = true;
		if($this->settings[$type]['disable'] == '1') {
			Tx_Formhandler_StaticFuncs::debugMessage('mail_disabled', $type);
			$doSend = false;
		} 
		
		$mailSettings = $this->settings[$type];
		$plain = $this->parseTemplate($type, 'plain');
		if(strlen(trim($plain)) > 0) {
			$template['plain'] = $plain;
		}
		$html = $this->parseTemplate($type, 'html');
		if(strlen(trim($html)) > 0) {
			$template['html'] = $html;
		}

		//init mailer object
		//$version = TYPO3_version;
		//$version = preg_replace('/[a-zA-Z]*[0-9]{0,1}$/','',$version);
		
		/*if(version_compare($version, '4.3.0', '>=')) {
			
			require_once(PATH_t3lib . 'class.t3lib_htmlmail.php');
			$emailObj = t3lib_div::makeInstance('t3lib_htmlmail');
		} else {*/
			require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.formhandler_htmlmail.php');
			$emailObj = t3lib_div::makeInstance('formhandler_htmlmail');
		//}
		
		$emailObj->start();

		//set e-mail options
		$emailObj->subject = $mailSettings['subject'];

		$sender = $mailSettings['sender_email'];
		if(isset($mailSettings['sender_email']) && is_array($mailSettings['sender_email'])) {
			$sender = implode(',', $mailSettings['sender_email']);
		}
		$emailObj->from_email = $sender;
		$emailObj->from_name = $mailSettings['sender_name'];

		$replyto = $mailSettings['replyto_email'];
		if(isset($mailSettings['replyto_email']) && is_array($mailSettings['replyto_email'])) {
			$replyto = implode(',', $mailSettings['replyto_email']);
		}
		$emailObj->replyto_email = $replyto;
		$emailObj->replyto_name = $mailSettings['replyto_name'];
		
		$cc = $mailSettings['cc_email'];
		if(isset($mailSettings['cc_email']) && is_array($mailSettings['cc_email'])) {
			$cc = implode(',', $mailSettings['cc_email']);
		}
		if($mailSettings['cc_name']) {
			$cc = $mailSettings['cc_name'] . ' <' . $cc . '>'; 
		}
		$emailObj->recipient_copy = $cc;
		
		$bcc = $mailSettings['bcc_email'];
		if(isset($mailSettings['bcc_email']) && is_array($mailSettings['bcc_email'])) {
			$bcc = implode(',', $mailSettings['bcc_email']);
		}
		if($mailSettings['bcc_name']) {
			$bcc = $mailSettings['bcc_name'] . ' <' . $bcc . '>'; 
		}
		$emailObj->recipient_blindcopy = $bcc;
		
		$returnPath = $mailSettings['return_path'];
		if(isset($mailSettings['return_path']) && is_array($mailSettings['return_path'])) {
			$returnPath = implode(',', $mailSettings['return_path']);
		}
		$emailObj->returnPath = $returnPath;
		
		if($mailSettings['email_header']) {
			$emailObj->additionalHeaders = $mailSettings['header'];
		}
		
		if(strlen(trim($template['plain'])) > 0) {
			$emailObj->setPlain($template['plain']);
		} else {
			$emailObj->setPlain(NULL);
		}

		if(strlen(trim($template['html'])) > 0) {
			if($mailSettings['htmlEmailAsAttachment']) {
				$prefix = 'formhandler_';
				if(isset($mailSettings['filePrefix.']['html'])) {
					$prefix = $mailSettings['filePrefix.']['html'];
				} elseif(isset($mailSettings['filePrefix'])) {
					$prefix = $mailSettings['filePrefix'];
				}
				$tmphtml = tempnam('typo3temp/', ('/' . $prefix)) . '.html';
				$tmphtml = str_replace('.tmp', '', $tmphtml);
				$tmphandle = fopen($tmphtml, 'wb');
				if ($tmphandle) {
					fwrite($tmphandle,$template['html']);
					fclose($tmphandle);
					Tx_Formhandler_StaticFuncs::debugMessage('adding_html', $tmphtml);
					$emailObj->addAttachment($tmphtml);
				}
			} else {
				$emailObj->setHtml($template['html']);
			}
		}

		if(!is_array($mailSettings['attachment'])) {
			$mailSettings['attachment'] = array($mailSettings['attachment']);
		}
		foreach($mailSettings['attachment'] as $attachment) {
			if(strlen($attachment) > 0) {
				$emailObj->addAttachment($attachment);
			}
		}
		if($mailSettings['attachPDF']) {
			Tx_Formhandler_StaticFuncs::debugMessage('adding_pdf', $mailSettings['attachPDF']);
			$emailObj->addAttachment($mailSettings['attachPDF']);
		}

		//parse max count of mails to send
		$count = 0;
		$max = $this->settings['limitMailsToUser'];
		if(!$max) {
			$max = 2;
		}
		if(!is_array($mailSettings['to_email'])) {
			$mailSettings['to_email'] = array($mailSettings['to_email']);
		}
		reset($mailSettings['to_email']);

		//send e-mails
		foreach($mailSettings['to_email'] as $mailto) {

			if($count < $max) {
				if (strstr($mailto, '@') && !eregi("\r", $mailto) && !eregi("\n", $mailto)) {
					$sent = false;
					if($doSend) {
						$emailObj->recipient = $mailto;
						$sent = $emailObj->send($mailto);
					}
				}
				$count++;
			}
			if($sent) {
				Tx_Formhandler_StaticFuncs::debugMessage('mail_sent', $mailto);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_subject', $emailObj->subject);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_sender', ($emailObj->from_name . ' &lt;' . $emailObj->from_email . '&gt;'), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_replyto', ($emailObj->replyto_name . ' &lt;' . $emailObj->replyto_email . '&gt;'), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_returnpath', $emailObj->returnPath, false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_cc', $emailObj->recipient_copy, false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_bcc', $emailObj->recipient_blindcopy, false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_plain', $template['plain'], false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_html', $template['html'], false);
			} else {
				Tx_Formhandler_StaticFuncs::debugMessage('mail_not_sent',$mailto);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_subject', $emailObj->subject);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_sender', ($emailObj->from_name . ' &lt;' . $emailObj->from_email . '&gt;'), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_replyto', ($emailObj->replyto_name . ' &lt;' . $emailObj->replyto_email . '&gt;'), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_returnpath', $emailObj->returnPath, false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_cc', str_replace('>', '&gt;', str_replace('<', '&lt;', $emailObj->recipient_copy)), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_bcc', str_replace('>', '&gt;', str_replace('<', '&lt;', $emailObj->recipient_blindcopy)), false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_plain', $template['plain'], false);
				Tx_Formhandler_StaticFuncs::debugMessage('mail_html', $template['html'], false);
			}
		}
		if($tmphtml) {
			unlink($tmphtml);
		}
	}

	/**
	 * Explodes the given list seperated by $sep. Substitutes values with according value in GET/POST, if set.
	 *
	 * @param string $list
	 * @param string $sep
	 * @return array
	 */
	private function explodeList($list, $sep = ',') {
		$items = t3lib_div::trimExplode($sep, $list);
		$splitArray = array();
		foreach($items as $item) {
			if(isset($this->gp[$item])) {
				array_push($splitArray, $this->gp[$item]);
			} else {
				array_push($splitArray, $item);
			}
		}
		return $splitArray;
	}

	/**
	 * Substitutes values with according value in GET/POST, if set.
	 *
	 * @param string $value
	 * @return string
	 */
	private function parseSettingValue($value) {
		if(isset($this->gp[$value])) {
			$parsed = $this->gp[$value];
		} else {
			$parsed = $value;
		}
		return $parsed;

	}

	/**
	 * Parses a setting in TypoScript and overrides it with setting in plugin record if set.
	 * The settings contains a single value or a TS object.
	 *
	 * @param array $settings The settings array containing the mail settings
	 * @param string $type admin|user
	 * @param string $key The key to parse in the settings array
	 * @return string
	 */
	private function parseValue($settings,$type,$key) {
		if(isset($this->emailSettings[$type][$key])) {
			$parsed = $this->parseSettingValue($this->emailSettings[$type][$key]);
		} else if(isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$parsed = $this->cObj->cObjGetSingle($settings[$key], $settings[$key . '.']);
		} else {
			$parsed = $this->parseSettingValue($settings[$key]);
		}
		return $parsed;
	}

	/**
	 * Parses a setting in TypoScript and overrides it with setting in plugin record if set.
	 * The settings contains a list of values or a TS object.
	 *
	 * @param array $settings The settings array containing the mail settings
	 * @param string $type admin|user
	 * @param string $key The key to parse in the settings array
	 * @return string|array
	 */
	private function parseList($settings, $type, $key) {
		if(isset($this->emailSettings[$type][$key])) {
			$parsed = $this->explodeList($this->emailSettings[$type][$key]);
		} else if(isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$parsed = $this->cObj->cObjGetSingle($settings[$key],$settings[$key . '.']);
		} else {
			$parsed = $this->explodeList($settings[$key]);
		}
		return $parsed;
	}

	/**
	 * Parses a list of file names or field names set in TypoScript and overrides it with setting in plugin record if set.
	 *
	 * @param array $settings The settings array containing the mail settings
	 * @param string $type admin|user
	 * @param string $key The key to parse in the settings array
	 * @return string
	 */
	private function parseFilesList($settings ,$type, $key) {
		if(isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$parsed = $this->cObj->cObjGetSingle($settings[$key],$settings[$key . '.']);
		} elseif($settings[$key]) {
			$files = t3lib_div::trimExplode(',', $settings[$key]);
			$parsed = array();
			session_start();
			foreach($files as $file) {
				if(isset($_SESSION['formhandlerFiles'][$file])) {
					foreach($_SESSION['formhandlerFiles'][$file] as $uploadedFile) {
						array_push($parsed,$uploadedFile['uploaded_path'] . $uploadedFile['uploaded_name']);
					}
				} else {
					array_push($parsed, $file);
				}
			}
		}
		return $parsed;
	}

	/**
	 * Substitutes markers like ###LLL:langKey### in given TypoScript settings array.
	 *
	 * @param array &$settings The E-Mail settings
	 * @return void
	 */
	protected function fillLangMarkersInSettings(&$settings) {
		foreach($settings as &$value) {
			if(isset($value) && is_array($value)) {
				$this->fillLangMarkersInSettings($value);
			} else {
				$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($value, $this->langFile);
				if(!empty($langMarkers)) {
					$value = $this->cObj->substituteMarkerArray($value, $langMarkers);
				}
			}
		}
	}

	/**
	 * Fetches the global TypoScript settings of the Formhandler
	 *
	 * @return void
	 */
	protected function getSettings() {
		return $this->configuration->getSettings();
	}

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @return void
	 */
	protected function init() {
		$this->langFile = Tx_Formhandler_StaticFuncs::readLanguageFiles($this->settings['langFile'], $this->settings);
		if(is_array($this->langFile)) {
			$this->langFile = $this->langFile[0];
		}
	}

	/**
	 * Method to set GET/POST for this class and load the configuration
	 *
	 * @param array The GET/POST values
	 * @param array The TypoScript configuration
	 * @return void
	 */
	public function loadConfig($gp,$tsConfig) {
		$this->gp = $gp;
		$this->settings = $tsConfig;
		$this->init();
		$this->settings = $this->parseEmailSettings($tsConfig);

		// Defines default values
		$defaultOptions = array(
			'templateFile' => 'template_file',
			'langFile' => 'lang_file',
		);
		foreach ($defaultOptions as $key => $option) {
			$_fileName = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], $option);
			if ($_fileName !== '') {
				$this->settings[$key] = $_fileName;
			}
		}

		// Unset unnecessary variables.
		unset($this->settings['admin.']);
		unset($this->settings['user.']);
	}

	/**
	 * Method to define whether the config is valid or not. If no, an exception is thrown.
	 *
	 */
	public function validateConfig() {
		if ($this->settings['templateFile'] == '') {
			Tx_Formhandler_StaticFuncs::throwException('no_template');
		}
	}

	/**
	 * Parses the email settings in flexform and stores them in an array.
	 *
	 * @param array The TypoScript configuration
	 * @return array The parsed email settings
	 */
	protected function parseEmailSettings($tsConfig) {
		$emailSettings = $tsConfig;
		$options = array (
			'filePrefix',
			'to_email',
			'subject',
			'sender_email',
			'sender_name',
			'replyto_email',
			'replyto_name',
			'cc_email',
			'cc_name',
			'bcc_email',
			'bcc_name',
			'to_name',
			'return_path',
			'attachment',
			'attachPDF',
			'htmlEmailAsAttachment'
		);

		$emailSettings['admin'] = $this->parseEmailSettingsByType($emailSettings['admin.'], 'admin', $options);
		$emailSettings['user'] = $this->parseEmailSettingsByType($emailSettings['user.'], 'user', $options);

		return $emailSettings;
	}

	/**
	 * Parses the email settings in flexform of a specific type (admin|user]
	 *
	 * @param array $currentSettings The current settings array containing the settings made via TypoScript
	 * @param string $type (admin|user)
	 * @param array $optionsToParse Array containing all option names to parse.
	 * @return array The parsed email settings
	 */
	private function parseEmailSettingsByType($currentSettings, $type, $optionsToParse = array()) {
		$typeLower = strtolower($type);
		$typeUpper = strtoupper($type);
		$section = 'sEMAIL' . $typeUpper;
		$emailSettings = $currentSettings;
		foreach($optionsToParse as $option) {
			$value = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], $option, $section);
			if(strlen($value) > 0) {
				$emailSettings[$option] = $value;
				if(isset($this->gp[$value])) {
					$emailSettings[$option] = $this->gp[$value];
				}

			} else {
				switch($option) {
					case 'to_email':
					case 'to_name':
					case 'sender_email':
					case 'replyto_email':
					case 'cc_email':
					case 'bcc_email':
					case 'return_path':
						$emailSettings[$option] = $this->parseList($currentSettings, $type, $option);
						break;

					case 'subject':
					case 'sender_name':
					case 'replyto_name':
					case 'cc_name':
					case 'bcc_name':
						$emailSettings[$option] = $this->parseValue($currentSettings, $type, $option);
						break;

					case 'attachment':
						$emailSettings[$option] = $this->parseFilesList($currentSettings,$type,$option);
						break;

					case 'attachPDF':
						if(isset($currentSettings['attachPDF.']) && is_array($currentSettings['attachPDF.'])) {
							$generatorClass = $currentSettings['attachPDF.']['class'];
							if(!$generatorClass) {
								$generatorClass = 'Tx_Formhandler_Generator_PDF';
							}
							$generatorClass = Tx_Formhandler_StaticFuncs::prepareClassName($generatorClass);
							$generator = $this->componentManager->getComponent($generatorClass);
							$exportFields = array();
							if($emailSettings['attachPDF.']['exportFields']) {
								$exportFields = t3lib_div::trimExplode(',', $currentSettings['attachPDF.']['exportFields']);
							}
							$prefix = 'formhandler_';
							if(isset($emailSettings['filePrefix.']['pdf'])) {
								$prefix = $emailSettings['filePrefix.']['pdf'];
							} elseif(isset($emailSettings['filePrefix'])) {
								$prefix = $emailSettings['filePrefix'];
							}
							$file = tempnam('typo3temp/', '/'. $prefix) . '.pdf';
							$file = str_replace('.tmp', '', $file);
							$templateFile = Tx_Formhandler_StaticFuncs::readTemplateFile($this->settings['templateFile'], $this->settings);
							$generator->setTemplateCode($templateFile);
							$generator->generateFrontendPDF($this->gp, $this->langFile, $exportFields, $file, true);
							$emailSettings['attachPDF'] = $file;
						} elseif ($currentSettings['attachPDF']) {
							$emailSettings['attachPDF'] = $currentSettings['attachPDF'];
						}
						break;

					case 'htmlEmailAsAttachment':
						if(isset($currentSettings['htmlEmailAsAttachment']) && !strcmp($currentSettings['htmlEmailAsAttachment'], '1')) {
							$emailSettings['htmlEmailAsAttachment'] = 1;
						}

						break;
					case 'filePrefix':
						if(isset($currentSettings['filePrefix'])) {
							$emailSettings['filePrefix'] = $currentSettings['filePrefix'];
						}
					break;
				}
			}
		}
		$this->fillLangMarkersInSettings($emailSettings);
		return $emailSettings;
	}

}
?>
