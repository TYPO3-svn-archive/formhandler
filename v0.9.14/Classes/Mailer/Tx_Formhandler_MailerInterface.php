<?php

interface Tx_Formhandler_MailerInterface {

	public function send($recipient);

	public function setHTML($html);
	public function setPlain($plain);

	public function setSubject($value);
	public function setSender($email, $name);
	public function setReplyTo($email, $name);
	public function addCc($email, $name);
	public function addBcc($email, $name);
	public function setReturnPath($value);

	public function addHeader($value);
	public function addAttachment($value);

	public function getHTML();
	public function getPlain();

	public function getSubject();
	public function getSender();
	public function getReplyTo();
	public function getCc();
	public function getBcc();
	public function getReturnPath();

}

?>