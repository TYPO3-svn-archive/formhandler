<?php
require_once(PATH_t3lib . 'class.t3lib_htmlmail.php');

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
 */
/**
 * HTML mail class for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class formhandler_htmlmail extends t3lib_htmlMail {
		
		// Headerinfo:
	var $recipient_copy	= '';	// This recipient (or list of...) will also receive the mail. Regard it as a copy.
	var $recipient_blindcopy = ''; // This recipient (or list of...) will also receive the mail as a blind copy. Regard it as a copy.

	/**
	 * Clears the header-string and sets the headers based on object-vars.
	 *
	 * @return	void
	 */
	public function setHeaders() {
		$this->headers = '';
			// Message_id
		$this->add_header('Message-ID: <'.$this->messageid.'>');
			// Return path
		if ($this->returnPath) {
			$this->add_header('Return-Path: '.$this->returnPath);
			$this->add_header('Errors-To: '.$this->returnPath);
		}
			// X-id
		if ($this->Xid) {
			$this->add_header('X-Typo3MID: '.$this->Xid);
		}

			// From
		if ($this->from_email) {
			if ($this->from_name && !t3lib_div::isBrokenEmailEnvironment()) {
				$this->add_header('From: '.$this->from_name.' <'.$this->from_email.'>');
			} else {
				$this->add_header('From: '.$this->from_email);
			}
		}

			// Cc
		if ($this->recipient_copy) {
			$this->add_header('Cc: ' . $this->recipient_copy);
		}

			// Bcc
		if ($this->recipient_blindcopy) {
			$this->add_header('Bcc: ' . $this->recipient_blindcopy);
		}

			// Reply
		if ($this->replyto_email) {
			if ($this->replyto_name) {
				$this->add_header('Reply-To: '.$this->replyto_name.' <'.$this->replyto_email.'>');
			} else {
				$this->add_header('Reply-To: '.$this->replyto_email);
			}
		}
			// Organisation
		if ($this->organisation) {
			$this->add_header('Organisation: '.$this->organisation);
		}
			// mailer
		if ($this->mailer) {
			$this->add_header('X-Mailer: '.$this->mailer);
		}
			// priority
		if ($this->priority) {
			$this->add_header('X-Priority: '.$this->priority);
		}
		$this->add_header('Mime-Version: 1.0');
		if($this->additionalHeaders) {
			$parts = t3lib_div::trimExplode("\n", $this->additionalHeaders);
			foreach($parts as $part) {
				$this->add_header($part);
			}
		}
		if (!$this->dontEncodeHeader) {
			$enc = $this->alt_base64 ? 'base64' : 'quoted_printable';	// Header must be ASCII, therefore only base64 or quoted_printable are allowed!
				// Quote recipient and subject
			$this->recipient = t3lib_div::encodeHeader($this->recipient,$enc,$this->charset);
			$this->subject = t3lib_div::encodeHeader($this->subject,$enc,$this->charset);
		}
	}

	/**
	 * Begins building the message-body
	 *
	 * @return	void
	 */
	public function setContent() {
		$this->message = '';
		$boundary = $this->getBoundary();

			// Setting up headers
		if (count($this->theParts['attach'])) {
			// Generate (plain/HTML) / attachments
			$this->add_header('Content-Type: multipart/mixed;');
			$this->add_header(' boundary="' . $boundary . '"');
			$this->add_message('This is a multi-part message in MIME format.' . "\n");
			$this->constructMixed($boundary);
		} elseif ($this->theParts['html']['content']) {
			if(strlen(trim($this->getContent('plain'))) > 0) {
				// Generate plain/HTML mail
				$this->add_header('Content-Type: ' . $this->getHTMLContentType() . ';');
				$this->add_header(' boundary="' . $boundary . '"');
				$this->add_message('This is a multi-part message in MIME format.' . "\n");
			} else {
				$this->add_header('Content-Type: text/html;');
				$this->add_header(' boundary="' . $boundary . '"');
				$this->constructHTML($boundary);
			}
			
			
			
		} elseif(strlen(trim($this->getContent('plain'))) > 0) {
			
			
			// Generate plain only
			$this->add_header($this->plain_text_header);
			$this->add_message($this->getContent('plain'));
		}
	}
	
	/**
	 * Here plain is combined with HTML
	 *
	 * @param	string		$boundary: the boundary to use
	 * @return	void
	 */
	public function constructAlternative($boundary) {
		$this->add_message('--'.$boundary);

			// plain is added
		if(strlen(trim($this->getContent('plain'))) > 0) {
			$this->add_message($this->plain_text_header);
			$this->add_message('');
			$this->add_message($this->getContent('plain'));
			$this->add_message('--' . $boundary);
		}

			// html is added
		$this->add_message($this->html_text_header);
		$this->add_message('');
		$this->add_message($this->getContent('html'));
		$this->add_message('--' . $boundary . '--' . "\n");
	}

	/**
	 * Sends the mail by calling the mail() function in php. On Linux systems this will invoke the MTA
	 * defined in php.ini (sendmail -t -i by default), on Windows a SMTP must be specified in the sys.ini.
	 * Most common MTA's on Linux has a Sendmail interface, including Postfix and Exim.
	 * For setting the return-path correctly, the parameter -f has to be added to the system call to sendmail.
	 * This obviously does not have any effect on Windows, but on Sendmail compliant systems this works. If safe mode
	 * is enabled, then extra parameters is not allowed, so a safe mode check is made before the mail() command is
	 * invoked. When using the -f parameter, some MTA's will put an X-AUTHENTICATION-WARNING saying that
	 * the return path was modified manually with the -f flag. To disable this warning make sure that the user running
	 * Apache is in the /etc/mail/trusted-users table.
	 *
	 * POSTFIX: With postfix version below 2.0 there is a problem that the -f parameter can not be used in conjunction
	 * with -t. Postfix will give an error in the maillog:
	 *
	 *  cannot handle command-line recipients with -t
	 *
	 * The -f parameter is only enabled if the parameter forceReturnPath is enabled in the install tool.
	 *
	 * This whole problem of return-path turns out to be quite tricky. If you have a solution that works better, on all
	 * standard MTA's then we are very open for suggestions.
	 *
	 * With time this function should be made such that several ways of sending the mail is possible (local MTA, smtp other).
	 *
	 * @return	boolean		Returns whether the mail was sent (successfully accepted for delivery)
	 */
	public function sendTheMail() {
		$mailWasSent = false;

			// Sending the mail requires the recipient and message to be set.
		if (!trim($this->recipient) || !trim($this->message)) {
			return false;
		}

			// On windows the -f flag is not used (specific for Sendmail and Postfix),
			// but instead the php.ini parameter sendmail_from is used.
		$returnPath = (strlen($this->returnPath) > 0) ? '-f ' . escapeshellarg($this->returnPath) : '';
		if($this->returnPath) {
			@ini_set('sendmail_from', t3lib_div::normalizeMailAddress($this->returnPath));
		}
		$recipient = t3lib_div::normalizeMailAddress($this->recipient);
		$recipient_copy = t3lib_div::normalizeMailAddress($this->recipient_copy);

		// If safe mode is on, the fifth parameter to mail is not allowed, so the fix wont work on unix with safe_mode=On
		$returnPathPossible = (!ini_get('safe_mode') && $this->forceReturnPath);
		if ($returnPathPossible) {
			$mailWasSent = mail($recipient,
				  $this->subject,
				  $this->message,
				  $this->headers,
				  $returnPath);
		} else {
			$mailWasSent = mail($recipient,
				  $this->subject,
				  $this->message,
				  $this->headers);
		}

			// Sending a copy
		if ($recipient_copy) {
			if ($returnPathPossible) {
				$mailWasSent = mail($recipient_copy,
					$this->subject,
					$this->message,
					$this->headers,
					$returnPath);
			} else {
				$mailWasSent = mail($recipient_copy,
					$this->subject,
					$this->message,
					$this->headers);
			}
		}
			// Auto response
		if ($this->auto_respond_msg) {
			$theParts = explode('/',$this->auto_respond_msg,2);
			$theParts[1] = str_replace("/",chr(10),$theParts[1]);
			if ($returnPathPossible) {
				$mailWasSent = mail($this->from_email,
					$theParts[0],
					$theParts[1],
					'From: ' . $recipient,
					$returnPath);
			} else {
				$mailWasSent = mail($this->from_email,
					$theParts[0],
					$theParts[1],
					'From: ' . $recipient);
			}
		}
		if ($this->returnPath) {
			ini_restore('sendmail_from');
		}
		return $mailWasSent;
	}
}

?>
