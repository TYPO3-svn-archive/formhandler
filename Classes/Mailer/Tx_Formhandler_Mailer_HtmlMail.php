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
 *                                                                        */

/**
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_Mailer_HtmlMail extends Tx_Formhandler_AbstractMailer implements Tx_Formhandler_MailerInterface {

	/**
	 * The internal email object to be used
	 *
	 * @access protected
	 * @var formhandler_htmlmail
	 */
	protected $emailObj;

	/* (non-PHPdoc)
	 * @see Classes/Component/Tx_Formhandler_AbstractClass#__construct()
	*/
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, 
								Tx_Formhandler_Configuration $configuration, 
								Tx_Formhandler_Globals $globals, 
								Tx_Formhandler_UtilityFuncs $utilityFuncs) {
	
		parent::__construct($componentManager, $configuration, $globals, $utilityFuncs);
		require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.formhandler_htmlmail.php');
		$this->emailObj = t3lib_div::makeInstance('formhandler_htmlmail');
		$this->emailObj->start();
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#send()
	*/
	public function send($recipient) {
		$sent = $this->emailObj->send($recipient);
		return $sent;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setHTML()
	*/
	public function setHTML($html) {
		$this->emailObj->setHtml($this->emailObj->encodeMsg($html));
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setPlain()
	*/
	public function setPlain($plain) {
		$this->emailObj->setPlain($this->emailObj->encodeMsg($plain));
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setSubject()
	*/
	public function setSubject($value) {
		$this->emailObj->subject = $value;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setSender()
	*/
	public function setSender($email, $name) {
		$this->emailObj->from_email = $email;
		$this->emailObj->from_name = $name;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setReplyTo()
	*/
	public function setReplyTo($email, $name) {
		$this->emailObj->replyto_email = $email;
		$this->emailObj->replyto_name = $name;	
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#addCc()
	*/
	public function addCc($email, $name) {
		if ($name) {
			$this->emailObj->recipient_copy[] = $name . ' <' . $email . '>';
		} else {
			$this->emailObj->recipient_copy[] = $email;
		}
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#addBcc()
	*/
	public function addBcc($email, $name) {
		if ($name) {
			$this->emailObj->recipient_blindcopy[] = $name . ' <' . $email . '>';
		} else {
			$this->emailObj->recipient_blindcopy[] = $email;
		}
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#setReturnPath()
	*/
	public function setReturnPath($value) {
		$this->emailObj->returnPath = $value;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#addHeader()
	*/
	public function addHeader($value) {

	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#addAttachment()
	*/
	public function addAttachment($value) {
		$this->emailObj->addAttachment($value);
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getHTML()
	*/
	public function getHTML() {
		return $this->emailObj->theParts['html']['content'];
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getPlain()
	*/
	public function getPlain() {
		return $this->emailObj->theParts['plain']['content'];
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getSubject()
	*/
	public function getSubject() {
		return $this->emailObj->subject;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getSender()
	*/
	public function getSender() {
		return $this->emailObj->from_name . ' <' . $this->emailObj->from_email . '>';
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getReplyTo()
	*/
	public function getReplyTo() {
		return $this->emailObj->replyto_name . ' <' . $this->emailObj->replyto_email . '>';
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getCc()
	*/
	public function getCc() {
		return $this->emailObj->recipient_copy;
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getBcc()
	*/
	public function getBcc() {
		return $this->emailObj->recipient_blindcopy;	
	}

	/* (non-PHPdoc)
	 * @see Classes/Mailer/Tx_Formhandler_MailerInterface#getReturnPath()
	*/
	public function getReturnPath() {
		return $this->emailObj->returnPath;
	}

}

?>