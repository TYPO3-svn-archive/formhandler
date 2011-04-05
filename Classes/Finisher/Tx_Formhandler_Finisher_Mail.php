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
 * finishers.2.config.user.attachPDF.class = Tx_Formhandler_Generator_TcPdf
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
		
		$viewClass = $this->settings['view'];
		if(!$viewClass) {
			$viewClass = 'Tx_Formhandler_View_Mail';
		}
		/* @var $view Tx_Formhandler_AbstractView */
		$view = $this->componentManager->getComponent($viewClass);
		
		$view->setLangFiles(Tx_Formhandler_Globals::$langFiles);
		$view->setPredefined($this->predefined);
		$view->setComponentSettings($this->settings);
		$templateCode = Tx_Formhandler_Globals::$templateCode;
		if($this->settings['templateFile']) {
			$templateCode = Tx_Formhandler_StaticFuncs::readTemplateFile(FALSE, $this->settings);
		}
		if($this->settings[$mode]['templateFile']) {
			$templateCode = Tx_Formhandler_StaticFuncs::readTemplateFile(FALSE, $this->settings[$mode]);
		}


		$view->setTemplate($templateCode, ('EMAIL_' . strtoupper($mode) . '_' . strtoupper($suffix) . Tx_Formhandler_Globals::$templateSuffix));
		if (!$view->hasTemplate()) {
			$view->setTemplate($templateCode, ('EMAIL_' . strtoupper($mode) . '_' . strtoupper($suffix)));
			if (!$view->hasTemplate()) {
				Tx_Formhandler_StaticFuncs::debugMessage('no_mail_template', array($mode, $suffix), 2);
			}
		}

		return $view->render($this->gp, array('mode' => $mode, 'suffix' => $suffix));
	}

	/**
	 * Sends mail according to given type.
	 *
	 * @param string $type (admin|user)
	 * @return void
	 */
	protected function sendMail($type) {
		$doSend = TRUE;
		if (intval($this->settings[$type]['disable']) === 1) {
			Tx_Formhandler_StaticFuncs::debugMessage('mail_disabled', array($type));
			$doSend = FALSE;
		} 

		$mailSettings = $this->settings[$type];
		$plain = $this->parseTemplate($type, 'plain');
		if (strlen(trim($plain)) > 0) {
			$template['plain'] = $plain;
		}
		$html = $this->parseTemplate($type, 'html');
		if (strlen(trim($html)) > 0) {
			$template['html'] = $html;
		}

		//init mailer object
		$emailClass = $this->settings['mailer.']['class'];
		if (!$emailClass) {
			$emailClass = 'Tx_Formhandler_Mailer_HtmlMail';
		}
		$emailClass = Tx_Formhandler_StaticFuncs::prepareClassName($emailClass);
		$emailObj = $this->componentManager->getComponent($emailClass);
		$emailObj->init($this->gp, $this->settings['mailer.']['config.']);

		//set e-mail options
		$emailObj->setSubject($mailSettings['subject']);

		$sender = $mailSettings['sender_email'];
		if (isset($mailSettings['sender_email']) && is_array($mailSettings['sender_email'])) {
			$sender = implode(',', $mailSettings['sender_email']);
		}

		$senderName = $mailSettings['sender_name'];
		if (isset($mailSettings['sender_name']) && is_array($mailSettings['sender_name'])) {
			$senderName = implode(',', $mailSettings['sender_name']);
		}

		$emailObj->setSender($sender, $senderName);

		$replyto = $mailSettings['replyto_email'];
		if (isset($mailSettings['replyto_email']) && is_array($mailSettings['replyto_email'])) {
			$replyto = implode(',', $mailSettings['replyto_email']);
		}

		$replytoName = $mailSettings['replyto_name'];
		if (isset($mailSettings['replyto_name']) && is_array($mailSettings['replyto_name'])) {
			$replytoName = implode(',', $mailSettings['replyto_name']);
		}
		$emailObj->setReplyTo($replyto, $replytoName);

		$cc = $mailSettings['cc_email'];
		if (!is_array($cc)) {
			$cc = array($cc);
		}

		$ccName = $mailSettings['cc_name'];
		if (!is_array($ccName)) {
			$ccName = array($ccName);
		}
		foreach ($cc as $key => $email) {
			$name = '';
			if (isset($ccName[$key])) {
				$name = $ccName[$key];
			}
			if (strlen($email) > 0) {
				$emailObj->addCc($email, $name);
			}
		}

		$bcc = $mailSettings['bcc_email'];
		if (!is_array($bcc)) {
			$bcc = array($bcc);
		}

		$bccName = $mailSettings['bcc_name'];
		if (!is_array($bccName)) {
			$bccName = array($bccName);
		}
		foreach ($bcc as $key => $email) {
			$name = '';
			if (isset($bccName[$key])) {
				$name = $bccName[$key];
			}
			if (strlen($email) > 0) {
				$emailObj->addBcc($email, $name);
			}
		}

		$returnPath = $mailSettings['return_path'];
		if (isset($mailSettings['return_path']) && is_array($mailSettings['return_path'])) {
			$returnPath = implode(',', $mailSettings['return_path']);
		}

		$emailObj->setReturnPath($returnPath);

		if ($mailSettings['email_header']) {
			$emailObj->addHeader($mailSettings['header']);
		}

		if (strlen(trim($template['plain'])) > 0) {
			$emailObj->setPlain($template['plain']);
		} else {
			$emailObj->setPlain(NULL);
		}

		if (strlen(trim($template['html'])) > 0) {
			if ($mailSettings['htmlEmailAsAttachment']) {
				$prefix = 'formhandler_';
				if (isset($mailSettings['filePrefix.']['html'])) {
					$prefix = $mailSettings['filePrefix.']['html'];
				} elseif (isset($mailSettings['filePrefix'])) {
					$prefix = $mailSettings['filePrefix'];
				}
				$tmphtml = tempnam('typo3temp/', ('/' . $prefix)) . '.html';
				$tmphtml = str_replace('.tmp', '', $tmphtml);
				$tmphandle = fopen($tmphtml, 'wb');
				if ($tmphandle) {
					fwrite($tmphandle, $template['html']);
					fclose($tmphandle);
					Tx_Formhandler_StaticFuncs::debugMessage('adding_html', array(), 1, array($template['html']));
					$emailObj->addAttachment($tmphtml);
				}
			} else {
				$emailObj->setHtml($template['html']);
			}
		}

		if (!is_array($mailSettings['attachment'])) {
			$mailSettings['attachment'] = array($mailSettings['attachment']);
		}
		foreach ($mailSettings['attachment'] as $idx => $attachment) {
			if (strlen($attachment) > 0) {
				$emailObj->addAttachment($attachment);
			}
		}
		if ($mailSettings['attachPDF']) {
			Tx_Formhandler_StaticFuncs::debugMessage('adding_pdf', array(), 1, array($mailSettings['attachPDF']));
			$emailObj->addAttachment($mailSettings['attachPDF']);
		}

		//parse max count of mails to send
		$count = 0;
		$max = $this->settings['limitMailsToUser'];
		if (!$max) {
			$max = 2;
		}
		if (!is_array($mailSettings['to_email'])) {
			$mailSettings['to_email'] = array($mailSettings['to_email']);
		}
		reset($mailSettings['to_email']);

		//send e-mails
		foreach ($mailSettings['to_email'] as $idx => $mailto) {
			$sent = FALSE;
			if ($count < $max) {
				if (strstr($mailto, '@') && !preg_match("/\r/i", $mailto) && !preg_match("/\n/i", $mailto)) {
					if ($doSend) {
						$sent = $emailObj->send($mailto);
					}
				}
				$count++;
			}
			if ($sent) {
				Tx_Formhandler_StaticFuncs::debugMessage('mail_sent', array($mailto));
			} else {
				Tx_Formhandler_StaticFuncs::debugMessage('mail_not_sent', array($mailto), 2);
			}
			Tx_Formhandler_StaticFuncs::debugMessage('mail_subject', array($emailObj->getSubject()));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_sender', array($emailObj->getSender()));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_replyto', array($emailObj->getReplyTo()));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_returnpath', array($emailObj->returnPath));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_cc', array(implode('<br />', $emailObj->getCc())));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_bcc', array(implode('<br />', $emailObj->getBcc())));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_plain', array(), 1, array($template['plain']));
			Tx_Formhandler_StaticFuncs::debugMessage('mail_html', array(), 1, array($template['html']));
		}
		if ($tmphtml) {
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
		foreach ($items as $idx => $item) {
			if (isset($this->gp[$item])) {
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
		if (isset($this->gp[$value])) {
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
		if (isset($this->emailSettings[$type][$key])) {
			$parsed = $this->parseSettingValue($this->emailSettings[$type][$key]);
		} else if (isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$settings[$key . '.']['gp'] = $this->gp;
			$parsed = Tx_Formhandler_StaticFuncs::getSingle($settings, $key);
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
		if (isset($this->emailSettings[$type][$key])) {
			$parsed = $this->explodeList($this->emailSettings[$type][$key]);
		} elseif (isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$parsed = Tx_Formhandler_StaticFuncs::getSingle($settings, $key);
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
		if (isset($settings[$key . '.']) && is_array($settings[$key . '.'])) {
			$parsed = Tx_Formhandler_StaticFuncs::getSingle($settings, $key);
			$parsed = t3lib_div::trimExplode(',', $parsed);
		} elseif ($settings[$key]) {
			$files = t3lib_div::trimExplode(',', $settings[$key]);
			$parsed = array();
			$sessionFiles = Tx_Formhandler_Globals::$session->get('files');
			foreach ($files as $idx => $file) {
				if (isset($sessionFiles[$file])) {
					foreach ($sessionFiles[$file] as $subIdx => $uploadedFile) {
						array_push($parsed, $uploadedFile['uploaded_path'] . $uploadedFile['uploaded_name']);
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
		foreach ($settings as &$value) {
			if (isset($value) && is_array($value)) {
				$this->fillLangMarkersInSettings($value);
			} else {
				$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($value, $this->langFile);
				if (!empty($langMarkers)) {
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
	 * Method to set GET/POST for this class and load the configuration
	 *
	 * @param array The GET/POST values
	 * @param array The TypoScript configuration
	 * @return void
	 */
	public function init($gp, $tsConfig) {
		$this->gp = $gp;
		$this->settings = $this->parseEmailSettings($tsConfig);

		// Defines default values
		$defaultOptions = array(
			'templateFile' => 'template_file',
			'langFile' => 'lang_file',
		);
		foreach ($defaultOptions as $key => $option) {
			$fileName = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], $option);
			if ($fileName) {
				$this->settings[$key] = $fileName;
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
			'htmlEmailAsAttachment',
			'plain.',
			'html.'
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
		foreach ($optionsToParse as $idx => $option) {
			$value = Tx_Formhandler_StaticFuncs::pi_getFFvalue($this->cObj->data['pi_flexform'], $option, $section);
			if (strlen($value) > 0) {
				$emailSettings[$option] = $value;
				if (isset($this->gp[$value])) {
					$emailSettings[$option] = $this->gp[$value];
				}

			} else {
				switch ($option) {
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
						if (isset($currentSettings['attachPDF.']) && is_array($currentSettings['attachPDF.'])) {
							$generatorClass = $currentSettings['attachPDF.']['class'];
							if ($generatorClass) {
								$generatorClass = Tx_Formhandler_StaticFuncs::prepareClassName($generatorClass);
								$generator = $this->componentManager->getComponent($generatorClass);
								$generator->init($this->gp, $currentSettings['attachPDF.']['config.']);
								$file = $generator->process();
								unset($currentSettings['attachPDF.']);
								$emailSettings['attachPDF'] = $file;
							}
						} elseif ($currentSettings['attachPDF']) {
							$emailSettings['attachPDF'] = $currentSettings['attachPDF'];
						}
						break;

					case 'htmlEmailAsAttachment':
						if (isset($currentSettings['htmlEmailAsAttachment']) && !strcmp($currentSettings['htmlEmailAsAttachment'], '1')) {
							$emailSettings['htmlEmailAsAttachment'] = 1;
						}

						break;
					case 'filePrefix':
						if (isset($currentSettings['filePrefix'])) {
							$emailSettings['filePrefix'] = $currentSettings['filePrefix'];
						}
						break;
					case 'plain.':
						if (isset($currentSettings['plain.'])) {
							$emailSettings['plain.'] = $currentSettings['plain.'];
						}
						break;
					case 'html.':
						if (isset($currentSettings['html.'])) {
							$emailSettings['html.'] = $currentSettings['html.'];
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
