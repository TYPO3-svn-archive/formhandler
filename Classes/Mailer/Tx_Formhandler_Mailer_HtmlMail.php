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
 * $Id: Tx_Formhandler_Finisher_Mail.php 24239 2009-09-10 09:17:47Z reinhardfuehricht $
 *                                                                        */

/**
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Mailer
 */
class Tx_Formhandler_Mailer_HtmlMail extends Tx_Formhandler_AbstractMailer implements Tx_Formhandler_MailerInterface {

	protected $emailObj;

	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		parent::__construct($componentManager, $configuration);
		require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.formhandler_htmlmail.php');
		$this->emailObj = t3lib_div::makeInstance('formhandler_htmlmail');
		$this->emailObj->start();
	}

	public function send($recipient) {
		$sent = $this->emailObj->send($recipient);
		return $sent;
	}

	public function setHTML($html) {
		$this->emailObj->setHtml($this->emailObj->encodeMsg($html));
	}

	public function setPlain($plain) {
		$this->emailObj->setPlain($this->emailObj->encodeMsg($plain));
	}

	public function setSubject($value) {
		$this->emailObj->subject = $value;
	}
	
	public function setSender($email, $name) {
		$this->emailObj->from_email = $email;
		$this->emailObj->from_name = $name;
	}

	public function setReplyTo($email, $name) {
		$this->emailObj->replyto_email = $email;
		$this->emailObj->replyto_name = $name;	
	}

	public function addCc($email, $name) {
		if ($name) {
			$this->emailObj->recipient_copy[] = $name . ' <' . $email . '>';
		} else {
			$this->emailObj->recipient_copy[] = $email;
		}
	}

	public function addBcc($email, $name) {
		if ($name) {
			$this->emailObj->recipient_blindcopy[] = $name . ' <' . $email . '>';
		} else {
			$this->emailObj->recipient_blindcopy[] = $email;
		}
	}

	public function setReturnPath($value) {
		$this->emailObj->returnPath = $value;
	}

	public function addHeader($value) {

	}

	public function addAttachment($value) {
		$this->emailObj->addAttachment($value);
	}

	public function getHTML() {
		return $this->emailObj->theParts['html']['content'];
	}

	public function getPlain() {
		return $this->emailObj->theParts['plain']['content'];
	}

	public function getSubject() {
		return $this->emailObj->subject;
	}

	public function getSender() {
		return $this->emailObj->from_name . ' <' . $this->emailObj->from_email . '>';
	}

	public function getReplyTo() {
		return $this->emailObj->replyto_name . ' <' . $this->emailObj->replyto_email . '>';
	}

	public function getCc() {
		return $this->emailObj->recipient_copy;
	}

	public function getBcc() {
		return $this->emailObj->recipient_blindcopy;	
	}

	public function getReturnPath() {
		return $this->emailObj->returnPath;
	}

}

?>