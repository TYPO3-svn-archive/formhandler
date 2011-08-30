<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * HTML mail class
 *
 * $Id: class.t3lib_htmlmail.php 4487 2008-11-25 09:16:07Z steffenk $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */

class formhandler_htmlmail {
		// Headerinfo:
	var $recipient		= '';
	var $recipient_copy	= array();	// This recipient (or list of...) will also receive the mail. Regard it as a copy.
	var $recipient_blindcopy = array();
	var $subject		= '';
	var $from_email		= '';
	var $from_name		= '';
	var $replyto_email	= '';
	var $replyto_name	= '';
	var $organisation	= '';
	var $priority		= 3;	// 1 = highest, 5 = lowest, 3 = normal
	var $mailer			= '';	// X-mailer, set to TYPO3 Major.Minor in constructor
	var $alt_base64		= 0;
	var $alt_8bit		= 0;
	var $jumperURL_prefix	= '';		// This is a prefix that will be added to all links in the mail. Example: 'http://www.mydomain.com/jump?userid=###FIELD_uid###&url='. if used, anything after url= is urlencoded.
	var $jumperURL_useId	= 0;			// If set, then the array-key of the urls are inserted instead of the url itself. Smart in order to reduce link-length
	var $mediaList		= '';				// If set, this is a list of the media-files (index-keys to the array) that should be represented in the html-mail
	var $http_password	= '';
	var $http_username	= '';
	var $postfix_version1	= false;

	// Internal
	/*
	This is how the $theParts-array is normally looking
	var $theParts = array(
		'plain' => array(
			'content' => ''
		),
		'html' => array(
			'content' => '',
			'path' => '',
			'media' => array(),
			'hrefs' => array()
		),
		'attach' => array()
	);
	*/
	var $theParts = array();

	var $messageid = '';
	var $returnPath = '';
	var $Xid = '';
	var $dontEncodeHeader = false;		// If set, the header will not be encoded

	var $headers = '';
	var $message = '';
	var $part = 0;
	var $image_fullpath_list = '';
	var $href_fullpath_list = '';

	var $plain_text_header = '';
	var $html_text_header = '';
	var $charset = '';
	var $defaultCharset = 'iso-8859-1';



	/**
	 * Constructor. If the configuration variable forceReturnPath is set,
	 * calls to mail will be called with a 5th parameter.
	 * See function sendTheMail for more info
	 *
	 * @return	void
	 */
	public function t3lib_htmlmail() {
		$this->forceReturnPath = $GLOBALS['TYPO3_CONF_VARS']['SYS']['forceReturnPath'];

		$this->mailer = 'TYPO3 '.TYPO3_version;
	}


	/**
	 * start action that sets the message ID and the charset
	 *
	 * @return	void
	 */
	public function start() {
		global $TYPO3_CONF_VARS;

			// Sets the message id
		$host = t3lib_div::getHostname();
		if (!$host || $host == '127.0.0.1' || $host == 'localhost' || $host == 'localhost.localdomain') {
			$host = ($TYPO3_CONF_VARS['SYS']['sitename'] ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $TYPO3_CONF_VARS['SYS']['sitename']) : 'localhost') . '.TYPO3';
		}
		$this->messageid = md5(microtime()) . '@' . $host;


			// Default line break for Unix systems.
		$this->linebreak = chr(10);
			// Line break for Windows. This is needed because PHP on Windows systems
			// send mails via SMTP instead of using sendmail, and thus the linebreak needs to be \r\n.
		if (TYPO3_OS == 'WIN') {
			$this->linebreak = chr(13).chr(10);
		}

			// Sets the Charset
		if (!$this->charset) {
			if (is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->renderCharset) {
				$this->charset = $GLOBALS['TSFE']->renderCharset;
			} elseif (is_object($GLOBALS['LANG']) && $GLOBALS['LANG']->charSet) {
				$this->charset = $GLOBALS['LANG']->charSet;
			} elseif ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
				$this->charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
			} else	{
				$this->charset = $this->defaultCharset;
			}
		}

			// Use quoted-printable headers by default
		$this->useQuotedPrintable();
	}


	/**
	 * sets the header of both Plain Text and HTML mails to quoted printable
	 *
	 * @return	void
	 */
	public function useQuotedPrintable() {
		$this->plain_text_header = 'Content-Type: text/plain; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: quoted-printable';
		$this->html_text_header = 'Content-Type: text/html; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: quoted-printable';
	}

	/**
	 * sets the encoding headers to base64 for both the Plain Text and HTML mail
	 *
	 * @return	void
	 */
	public function useBase64() {
		$this->plain_text_header = 'Content-Type: text/plain; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: base64';
		$this->html_text_header = 'Content-Type: text/html; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: base64';
		$this->alt_base64 = 1;
	}


	/**
	 * sets the encoding to 8bit and the current charset of both the Plain Text and the HTML mail
	 *
	 * @return	void
	 */
	public function use8Bit() {
		$this->plain_text_header = 'Content-Type: text/plain; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: 8bit';
		$this->html_text_header = 'Content-Type: text/html; charset='.$this->charset.$this->linebreak.'Content-Transfer-Encoding: 8bit';
		$this->alt_8bit = 1;
	}


	/**
	 * Encodes the message content according to the options "alt_base64" and "alt_8bit" (no need to encode here)
	 * or to "quoted_printable" if no option is set.
	 *
	 * @param	string		$content the content that will be encoded
	 * @return	string		the encoded content
	 */
	public function encodeMsg($content) {
		if ($this->alt_base64) {
			return $this->makeBase64($content);
		} elseif ($this->alt_8bit) {
			return $content;
		} else	{
			return t3lib_div::quoted_printable($content);
		}
	}


	/**
	 * Adds plain-text, replaces the HTTP urls in the plain text and then encodes it
	 *
	 * @param	string		$content that will be added
	 * @return	void
	 */
	public function addPlain($content) {
		$content = $this->substHTTPurlsInPlainText($content);
		$this->setPlain($this->encodeMsg($content));
	}


	/**
	 * Adds an attachment to the mail
	 *
	 * @param	string		$file: the filename to add
	 * @return	boolean		whether the attachment was added or not
	 */
	public function addAttachment($file) {
			// Fetching the content and the mime-type
		$fileInfo = $this->getExtendedURL($file);
		if ($fileInfo) {
			if (!$fileInfo['content_type']) {
				$fileInfo['content_type'] = 'application/octet-stream';
			}
			$temp = $this->split_fileref($file);
			if ($temp['file']) {
				$fileInfo['filename'] = $temp['file'];
			} elseif (strpos(' '.$fileInfo['content_type'], 'htm')) {
				$fileInfo['filename'] = 'index.html';
			} else	{
				$fileInfo['filename'] = 'unknown';
			}
			$this->theParts['attach'][] = $fileInfo;
			return true;
		}
		return false;
	}


	/**
	 * Adds HTML and media, encodes it from a URL or file
	 *
	 * @param	string		$file: the filename to add
	 * @return	boolean		whether the attachment was added or not
	 */
	public function addHTML($file) {
		$status = $this->fetchHTML($file);
		if (!$status) {
			return false;
		}
		if ($this->extractFramesInfo()) {
			return 'Document was a frameset. Stopped';
		}
		$this->extractMediaLinks();
		$this->extractHyperLinks();
		$this->fetchHTMLMedia();
		$this->substMediaNamesInHTML(0);	// 0 = relative
		$this->substHREFsInHTML();
		$this->setHtml($this->encodeMsg($this->theParts['html']['content']));
	}


	/**
	 * Extract HTML-parts, used externally
	 *
	 * @param	string		$html: will be added to the html "content" part
	 * @param	string		$url: will be added to the html "path" part
	 * @return	void
	 */
	public function extractHtmlInit($html,$url) {
		$this->theParts['html']['content'] = $html;
		$this->theParts['html']['path'] = $url;
	}


	/**
	 * Assembles the message by headers and content and finally send it to the provided recipient.
	 *
	 * @param	string		$recipient: The recipient the message should be delivered to (if blank, $this->recipient will be used instead)
	 * @return	boolean		Returns whether the mail was sent (successfully accepted for delivery)
	 */
	public function send($recipient) {
		if ($recipient) {
			$this->recipient = $recipient;
		}
		$this->setHeaders();
		$this->setContent();
		$mailWasSent = $this->sendTheMail();
		return $mailWasSent;
	}













	/*****************************************
	 *
	 * Main functions
	 *
	 *****************************************/

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
			// Reply
		if ($this->replyto_email) {
			if ($this->replyto_name) {
				$this->add_header('Reply-To: '.$this->replyto_name.' <'.$this->replyto_email.'>');
			} else {
				$this->add_header('Reply-To: '.$this->replyto_email);
			}
		}
		
		// Cc
		if (!empty($this->recipient_copy)) {
			$this->add_header('Cc: ' . implode(',', $this->recipient_copy));
		}

			// Bcc
		if (!empty($this->recipient_blindcopy)) {
			$this->add_header('Bcc: ' . implode(',', $this->recipient_blindcopy));
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

		if (!$this->dontEncodeHeader) {
			$enc = $this->alt_base64 ? 'base64' : 'quoted_printable';	// Header must be ASCII, therefore only base64 or quoted_printable are allowed!
				// Quote recipient and subject
			$this->recipient = t3lib_div::encodeHeader($this->recipient,$enc,$this->charset);
			$this->subject = t3lib_div::encodeHeader($this->subject,$enc,$this->charset);
		}
	}


	/**
	 * Sets the recipient(s). If you supply a string, you set one recipient.
	 * If you supply an array, every value is added as a recipient.
	 *
	 * @param	mixed		$recipient: the recipient(s) to set
	 * @return	void
	 */
	public function setRecipient($recipient) {
		$this->recipient = (is_array($recipient) ? implode(',', $recipient) : $recipient);
	}


	/**
	 * Returns the content type based on whether the mail has media / attachments or no
	 *
	 * @return	string		the content type
	 */
	public function getHTMLContentType() {
		return (count($this->theParts['html']['media']) ? 'multipart/related' : 'multipart/alternative');
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
			// Generate plain/HTML mail
			$this->add_header('Content-Type: ' . $this->getHTMLContentType() . ';');
			$this->add_header(' boundary="' . $boundary . '"');
			$this->add_message('This is a multi-part message in MIME format.' . "\n");
			$this->constructHTML($boundary);
		} else {
			// Generate plain only
			$this->add_header($this->plain_text_header);
			$this->add_message($this->getContent('plain'));
		}
	}


	/**
	 * This functions combines the plain / HTML content with the attachments
	 *
	 * @param	string		$boundary: the mail boundary
	 * @return	void
	 */
	public function constructMixed($boundary) {
		$this->add_message('--' . $boundary);

		if ($this->theParts['html']['content']) {
			// HTML and plain is added
			$newBoundary = $this->getBoundary();
			$this->add_message('Content-Type: '.$this->getHTMLContentType() . ';');
			$this->add_message(' boundary="' . $newBoundary . '"');
			$this->add_message('');
			$this->constructHTML($newBoundary);
		} else {
			// Purely plain
			$this->add_message($this->plain_text_header);
			$this->add_message('');
			$this->add_message($this->getContent('plain'));
		}
		// attachments are added
		if (is_array($this->theParts['attach'])) {
			foreach ($this->theParts['attach'] as $media) {
				$this->add_message('--' . $boundary);
				$this->add_message('Content-Type: ' . $media['content_type'] . ';');
				$this->add_message(' name="' . $media['filename'] . '"');
				$this->add_message('Content-Transfer-Encoding: base64');
				$this->add_message('Content-Disposition: attachment;');
				$this->add_message(' filename="' . $media['filename'] . '"');
				$this->add_message('');
				$this->add_message($this->makeBase64($media['content']));
			}
		}
		$this->add_message('--' . $boundary . '--' . "\n");
	}


	/**
	 * this function creates the HTML part of the mail
	 *
	 * @param	string		$boundary: the boundary to use
	 * @return	void
	 */
	public function constructHTML($boundary) {
		// If media, then we know, the multipart/related content-type has been set before this function call
		if (count($this->theParts['html']['media'])) {
			$this->add_message('--' . $boundary);
			// HTML has media
			$newBoundary = $this->getBoundary();
			$this->add_message('Content-Type: multipart/alternative;');
			$this->add_message(' boundary="' . $newBoundary . '"');
			$this->add_message('Content-Transfer-Encoding: 7bit');
			$this->add_message('');

				// Adding the plaintext/html mix, and use $newBoundary
			$this->constructAlternative($newBoundary);
			$this->constructHTML_media($boundary);
		} else	{
			// if no media, just use the $boundary for adding plaintext/html mix
			$this->constructAlternative($boundary);
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
		$this->add_message($this->plain_text_header);
		$this->add_message('');
		$this->add_message($this->getContent('plain'));
		$this->add_message('--' . $boundary);

			// html is added
		$this->add_message($this->html_text_header);
		$this->add_message('');
		$this->add_message($this->getContent('html'));
		$this->add_message('--' . $boundary . '--' . "\n");
	}


	/**
	 * Constructs the HTML-part of message if the HTML contains media
	 *
	 * @param	string		$boundary: the boundary to use
	 * @return	void
	 */
	public function constructHTML_media($boundary) {
		// media is added
		if (is_array($this->theParts['html']['media'])) {
			foreach($this->theParts['html']['media'] as $key => $media) {
				if (!$this->mediaList || t3lib_div::inList($this->mediaList, $key)) {
					$this->add_message('--' . $boundary);
					$this->add_message('Content-Type: ' . $media['ctype']);
					$this->add_message('Content-ID: <part' . $key . '.' . $this->messageid . '>');
					$this->add_message('Content-Transfer-Encoding: base64');
					$this->add_message('');
					$this->add_message($this->makeBase64($media['content']));
				}
			}
		}
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


	/**
	 * Returns boundaries
	 *
	 * @return	string	the boundary
	 */
	public function getBoundary() {
		$this->part++;
		return 	"----------".uniqid("part_".$this->part."_");
	}


	/**
	 * Sets the plain-text part. No processing done.
	 *
	 * @param	string		$content: the plain content
	 * @return	void
	 */
	public function setPlain($content) {
		$this->theParts['plain']['content'] = $content;
	}


	/**
	 * Sets the HTML-part. No processing done.
	 *
	 * @param	string		$content: the HTML content
	 * @return	void
	 */
	public function setHtml($content) {
		$this->theParts['html']['content'] = $content;
	}


	/**
	 * Adds a header to the mail. Use this AFTER the setHeaders()-function
	 *
	 * @param	string		$header: the header in form of "key: value"
	 * @return	void
	 */
	public function add_header($header) {
			// Mail headers must be ASCII, therefore we convert the whole header to either base64 or quoted_printable
		if (!$this->dontEncodeHeader && !stristr($header,'Content-Type') && !stristr($header,'Content-Transfer-Encoding')) {
				// Field tags must not be encoded
			$parts = explode(': ',$header,2);
			if (count($parts) == 2) {
				$enc = $this->alt_base64 ? 'base64' : 'quoted_printable';
				$parts[1] = t3lib_div::encodeHeader($parts[1], $enc, $this->charset);
				$header = implode(': ', $parts);
			}
		}

		$this->headers .= $header."\n";
	}


	/**
	 * Adds a line of text to the mail-body. Is normally used internally
	 *
	 * @param	string		$msg: the message to add
	 * @return	void
	 */
	public function add_message($msg) {
		$this->message .= $msg."\n";
	}


	/**
	 * returns the content specified by the type (plain, html etc.)
	 *
	 * @param	string		$type: the content type, can either plain or html
	 * @return	void
	 */
	public function getContent($type) {
		return $this->theParts[$type]['content'];
	}


	/**
	 * shows a preview of the email of the headers and the message
	 *
	 * @return	void
	 */
	public function preview() {
		echo nl2br(htmlspecialchars($this->headers));
		echo "<BR>";
		echo nl2br(htmlspecialchars($this->message));
	}








	/****************************************************
	 *
	 * Functions for acquiring attachments, HTML, analyzing and so on  **
	 *
	 ***************************************************/

	/**
	 * Fetches the HTML-content from either url og local serverfile
	 *
	 * @param	string		$file: the file to load
	 * @return	boolean		whether the data was fetched or not
	 */
	public function fetchHTML($file) {
			// Fetches the content of the page
		$this->theParts['html']['content'] = $this->getURL($file);
		if ($this->theParts['html']['content']) {
			$addr = $this->extParseUrl($file);
 			$path = ($addr['scheme']) ? $addr['scheme'].'://'.$addr['host'].(($addr['port'])?':'.$addr['port']:'').(($addr['filepath'])?$addr['filepath']:'/') : $addr['filepath'];
			$this->theParts['html']['path'] = $path;
			return true;
		} else	{
			return false;
		}
	}


	/**
	 * Fetches the mediafiles which are found by extractMediaLinks()
	 *
	 * @return	void
	 */
	public function fetchHTMLMedia() {
		if (!is_array($this->theParts['html']['media']) || !count($this->theParts['html']['media'])) return;
		foreach ($this->theParts['html']['media'] as $key => $media) {
				// fetching the content and the mime-type
			$picdata = $this->getExtendedURL($this->theParts['html']['media'][$key]['absRef']);
			if (is_array($picdata)) {
				$this->theParts['html']['media'][$key]['content'] = $picdata['content'];
				$this->theParts['html']['media'][$key]['ctype']   = $picdata['content_type'];
			}
		}
	}


	/**
	 * extracts all media-links from $this->theParts['html']['content']
	 *
	 * @return	void
	 */
	public function extractMediaLinks() {
		$html_code = $this->theParts['html']['content'];
		$attribRegex = $this->tag_regex(array('img','table','td','tr','body','iframe','script','input','embed'));

			// split the document by the beginning of the above tags
		$codepieces = explode($attribRegex, $html_code);
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		$reg = array();
		for ($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($html_code,$len+1,10),' '));
			$len += strlen($tag)+strlen($codepieces[$i])+2;
			$dummy = preg_match("/[^>]*/i", $codepieces[$i], $reg);
			$attributes = $this->get_tag_attributes($reg[0]);	// Fetches the attributes for the tag
			$imageData = array();

				// Finds the src or background attribute
			$imageData['ref'] = ($attributes['src'] ? $attributes['src'] : $attributes['background']);
			if ($imageData['ref']) {
					// find out if the value had quotes around it
				$imageData['quotes'] = (substr($codepieces[$i], strpos($codepieces[$i], $imageData['ref'])-1,1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$imageData['subst_str'] = $imageData['quotes'].$imageData['ref'].$imageData['quotes'];
				if ($imageData['ref'] && !strstr($this->image_fullpath_list,"|".$imageData["subst_str"]."|")) {
					$this->image_fullpath_list .= "|".$imageData['subst_str']."|";
					$imageData['absRef'] = $this->absRef($imageData['ref']);
					$imageData['tag'] = $tag;
					$imageData['use_jumpurl'] = $attributes['dmailerping']?1:0;
					$this->theParts['html']['media'][] = $imageData;
				}
			}
		}

			// Extracting stylesheets
		$attribRegex = $this->tag_regex(array('link'));
			// Split the document by the beginning of the above tags
		$codepieces = explode($attribRegex, $html_code);
		$pieces = count($codepieces);
		for ($i = 1; $i < $pieces; $i++) {
			$dummy = preg_match("/[^>]*/i", $codepieces[$i], $reg);
				// fetches the attributes for the tag
			$attributes = $this->get_tag_attributes($reg[0]);
			$imageData = array();
			if (strtolower($attributes['rel']) == 'stylesheet' && $attributes['href']) {
					// Finds the src or background attribute
				$imageData['ref'] = $attributes['href'];
					// Finds out if the value had quotes around it
				$imageData['quotes'] = (substr($codepieces[$i],strpos($codepieces[$i], $imageData['ref'])-1,1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$imageData['subst_str'] = $imageData['quotes'].$imageData['ref'].$imageData['quotes'];
				if ($imageData['ref'] && !strstr($this->image_fullpath_list,"|".$imageData["subst_str"]."|")) {
					$this->image_fullpath_list .= "|".$imageData["subst_str"]."|";
					$imageData['absRef'] = $this->absRef($imageData["ref"]);
					$this->theParts['html']['media'][] = $imageData;
				}
			}
		}

			// fixes javascript rollovers
		$codepieces = explode(quotemeta(".src"), $html_code);
		$pieces = count($codepieces);
		$expr = "^[^".quotemeta("\"").quotemeta("'")."]*";
		for($i = 1; $i < $pieces; $i++) {
			$temp = $codepieces[$i];
			$temp = trim(preg_replace("=","",trim($temp)));
			preg_match($expr,substr($temp,1,strlen($temp)),$reg);
			$imageData['ref'] = $reg[0];
			$imageData['quotes'] = substr($temp,0,1);
				// subst_str is the string to look for, when substituting lateron
			$imageData['subst_str'] = $imageData['quotes'].$imageData['ref'].$imageData['quotes'];
			$theInfo = $this->split_fileref($imageData['ref']);

			switch ($theInfo['fileext']) {
				case 'gif':
				case 'jpeg':
				case 'jpg':
					if ($imageData['ref'] && !strstr($this->image_fullpath_list,"|".$imageData["subst_str"]."|")) {
						$this->image_fullpath_list .= "|".$imageData['subst_str']."|";
						$imageData['absRef'] = $this->absRef($imageData['ref']);
						$this->theParts['html']['media'][] = $imageData;
					}
				break;
			}
		}
	}


	/**
	 * extracts all hyper-links from $this->theParts["html"]["content"]
	 *
	 * @return	void
	 */
	public function extractHyperLinks() {
		$html_code = $this->theParts['html']['content'];
		$attribRegex = $this->tag_regex(array('a','form','area'));
		$codepieces = explode($attribRegex, $html_code);	// Splits the document by the beginning of the above tags
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		for($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($html_code,$len+1,10)," "));
			$len += strlen($tag) + strlen($codepieces[$i]) + 2;

			$dummy = preg_match("/[^>]*/i", $codepieces[$i], $reg);
				// Fetches the attributes for the tag
			$attributes = $this->get_tag_attributes($reg[0]);
			$hrefData = array();
			$hrefData['ref'] = ($attributes['href'] ? $attributes['href'] : $hrefData['ref'] = $attributes['action']);
			if ($hrefData['ref']) {
					// Finds out if the value had quotes around it
				$hrefData['quotes'] = (substr($codepieces[$i],strpos($codepieces[$i], $hrefData["ref"])-1,1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$hrefData['subst_str'] = $hrefData['quotes'].$hrefData['ref'].$hrefData['quotes'];
				if ($hrefData['ref'] && substr(trim($hrefData['ref']),0,1) != "#" && !strstr($this->href_fullpath_list,"|".$hrefData['subst_str']."|")) {
					$this->href_fullpath_list .= "|".$hrefData['subst_str']."|";
					$hrefData['absRef'] = $this->absRef($hrefData['ref']);
					$hrefData['tag'] = $tag;
					$this->theParts['html']['hrefs'][] = $hrefData;
				}
			}
		}
			// Extracts TYPO3 specific links made by the openPic() JS function
		$codepieces = explode("onClick=\"openPic('", $html_code);
		$pieces = count($codepieces);
		for($i = 1; $i < $pieces; $i++) {
			$showpic_linkArr = explode("'",$codepieces[$i]);
			$hrefData['ref'] = $showpic_linkArr[0];
			if ($hrefData['ref']) {
				$hrefData['quotes'] = "'";
					// subst_str is the string to look for, when substituting lateron
				$hrefData['subst_str'] = $hrefData['quotes'].$hrefData['ref'].$hrefData['quotes'];
				if ($hrefData['ref'] && !strstr($this->href_fullpath_list,"|".$hrefData['subst_str']."|")) {
					$this->href_fullpath_list .= "|".$hrefData['subst_str']."|";
					$hrefData['absRef'] = $this->absRef($hrefData['ref']);
					$this->theParts['html']['hrefs'][] = $hrefData;
				}
			}
		}
	}


	/**
	 * extracts all media-links from $this->theParts["html"]["content"]
	 *
	 * @return	array	two-dimensional array with information about each frame
	 */
	public function extractFramesInfo() {
		$htmlCode = $this->theParts['html']['content'];
		$info = array();
		if (strpos(' '.$htmlCode,'<frame ')) {
			$attribRegex = $this->tag_regex('frame');
				// Splits the document by the beginning of the above tags
			$codepieces = explode($attribRegex, $htmlCode, 1000000);
			$pieces = count($codepieces);
			for($i = 1; $i < $pieces; $i++) {
				$dummy = preg_match("/[^>]*/i", $codepieces[$i], $reg);
					// Fetches the attributes for the tag
				$attributes = $this->get_tag_attributes($reg[0]);
				$frame = array();
				$frame['src'] = $attributes['src'];
				$frame['name'] = $attributes['name'];
				$frame['absRef'] = $this->absRef($frame['src']);
				$info[] = $frame;
			}
			return $info;
		}
	}


	/**
	 * This function substitutes the media-references in $this->theParts["html"]["content"]
	 *
	 * @param	boolean		$absolute: If true, then the refs are substituted with http:// ref's indstead of Content-ID's (cid).
	 * @return	void
	 */
	public function substMediaNamesInHTML($absolute) {
		if (is_array($this->theParts['html']['media'])) {
			foreach ($this->theParts['html']['media'] as $key => $val) {
				if ($val['use_jumpurl'] && $this->jumperURL_prefix) {
					$subst = $this->jumperURL_prefix.t3lib_div::rawUrlEncodeFP($val['absRef']);
				} else {
					$subst = ($absolute) ? $val['absRef'] : 'cid:part'.$key.'.'.$this->messageid;
				}
				$this->theParts['html']['content'] = str_replace(
					$val['subst_str'],
					$val['quotes'] . $subst . $val['quotes'],
					$this->theParts['html']['content']);
			}
		}
		if (!$absolute) {
			$this->fixRollOvers();
		}
	}


	/**
	 * This function substitutes the hrefs in $this->theParts["html"]["content"]
	 *
	 * @return	void
	 */
	public function substHREFsInHTML() {
		if (!is_array($this->theParts['html']['hrefs'])) return;
		foreach ($this->theParts['html']['hrefs'] as $key => $val) {
				// Form elements cannot use jumpurl!
			if ($this->jumperURL_prefix && $val['tag'] != 'form') {
				if ($this->jumperURL_useId) {
					$substVal = $this->jumperURL_prefix.$key;
				} else {
					$substVal = $this->jumperURL_prefix.t3lib_div::rawUrlEncodeFP($val['absRef']);
				}
			} else {
				$substVal = $val['absRef'];
			}
			$this->theParts['html']['content'] = str_replace(
				$val['subst_str'],
				$val['quotes'] . $substVal . $val['quotes'],
				$this->theParts['html']['content']);
		}
	}


	/**
	 *  This substitutes the http:// urls in plain text with links
	 *
	 * @param	string		$content: the content to use to substitute
	 * @return	string		the changed content
	 */
	public function substHTTPurlsInPlainText($content) {
		if (!$this->jumperURL_prefix) return $content;

		$textpieces = explode("http://", $content);
		$pieces = count($textpieces);
		$textstr = $textpieces[0];
		for($i = 1; $i<$pieces; $i++) {
			$len = strcspn($textpieces[$i],chr(32).chr(9).chr(13).chr(10));
			if (trim(substr($textstr,-1)) == '' && $len) {
				$lastChar = substr($textpieces[$i],$len-1,1);
				if (!preg_match("/[A-Za-z0-9\/#]/",$lastChar)) {
					// Included "\/" 3/12
					$len--;
				}

				$parts = array();
				$parts[0] = "http://".substr($textpieces[$i],0,$len);
				$parts[1] = substr($textpieces[$i],$len);

				if ($this->jumperURL_useId) {
					$this->theParts['plain']['link_ids'][$i] = $parts[0];
					$parts[0] = $this->jumperURL_prefix.'-'.$i;
				} else {
					$parts[0] = $this->jumperURL_prefix.t3lib_div::rawUrlEncodeFP($parts[0]);
				}
				$textstr .= $parts[0].$parts[1];
			} else {
				$textstr .= 'http://'.$textpieces[$i];
			}
		}
		return $textstr;
	}


	/**
	 * JavaScript rollOvers cannot support graphics inside of mail.
	 * If these exists we must let them refer to the absolute url. By the way:
	 * Roll-overs seems to work only on some mail-readers and so far I've seen it
	 * work on Netscape 4 message-center (but not 4.5!!)
	 *
	 * @return	void
	 */
	public function fixRollOvers() {
		$newContent = '';
		$items = explode('.src',$this->theParts['html']['content']);
		if (count($items) <= 1)	return;

		foreach($items as $key => $part) {
			$sub = substr($part, 0, 200);
			if (preg_match("/cid:part[^ \"']*/",$sub,$reg)) {
					// The position of the string
				$thePos = strpos($part,$reg[0]);
					// Finds the id of the media...
				preg_match("/cid:part([^\.]*).*/",$sub,$reg2);
				$theSubStr = $this->theParts['html']['media'][intval($reg2[1])]['absRef'];
				if ($thePos && $theSubStr) {
					// ... and substitutes the javaScript rollover image with this instead
					// If the path is NOT and url, the reference is set to nothing
					if (!strpos(' '.$theSubStr, 'http://')) {
						$theSubStr = 'http://';
					}
					$part = substr($part, 0, $thePos) . $theSubStr . substr($part,$thePos+strlen($reg[0]),strlen($part));
				}
			}
			$newContent .= $part . ((($key+1) != count($items)) ? '.src' : '');
		}
		$this->theParts['html']['content'] = $newContent;
	}








	/*******************************************
	 *
	 * File and URL-functions
	 *
	 *******************************************/

	/**
	 * Returns base64-encoded content, which is broken every 76 character
	 *
	 * @param	string		$inputstr: the string to encode
	 * @return	string		the encoded string
	 */
	public function makeBase64($inputstr) {
		return chunk_split(base64_encode($inputstr));
	}


	/**
	 * reads the URL or file and determines the Content-type by either guessing or opening a connection to the host
	 *
	 * @param	string		$url: the URL to get information of
	 * @return	mixed		either false or the array with information
	 */
	public function getExtendedURL($url) {
		$res = array();
		$res['content'] = $this->getURL($url);
		if (!$res['content']) return false;
		$pathInfo = parse_url($url);
		$fileInfo = $this->split_fileref($pathInfo['path']);
		switch ($fileInfo['fileext']) {
			case 'gif':
			case 'png':
				$res['content_type'] = 'image/'.$fileInfo['fileext'];
				break;
			case 'jpg':
			case 'jpeg':
				$res['content_type'] = 'image/jpeg';
				break;
			case 'html':
			case 'htm':
				$res['content_type'] = 'text/html';
				break;
			case 'css':
				$res['content_type'] = 'text/css';
				break;
			case 'swf':
				$res['content_type'] = 'application/x-shockwave-flash';
				break;
			default:
				$res['content_type'] = $this->getMimeType($url);
		}
		return $res;
	}


	/**
	 * Adds HTTP user and password (from $this->http_username) to a URL
	 *
	 * @param	string		$url: the URL
	 * @return	string		the URL with the added values
	 */
	public function addUserPass($url) {
		$user = $this->http_username;
		$pass = $this->http_password;
		$matches = array();
		if ($user && $pass && preg_match('/^(https?:\/\/)/', $url, $matches)) {
			return $matches[1].$user.':'.$pass.'@'.substr($url,strlen($matches[1]));
		}
		return $url;
	}


	/**
	 * reads a url or file
	 *
	 * @param	string		$url: the URL to fetch
	 * @return	string		the content of the URL
	 */
	public function getURL($url) {
		$url = $this->addUserPass($url);
		return t3lib_div::getURL($url);
	}


	/**
	 * reads a url or file and strips the HTML-tags AND removes all
	 * empty lines. This is used to read plain-text out of a HTML-page
	 *
	 * @param	string		$url: the URL to load
	 * @return	the content
	 */
	public function getStrippedURL($url) {
		$content = '';
		if ($fd = fopen($url, "rb")) {
			while (!feof($fd)) {
				$line = fgetss($fd, 5000);
				if (trim($line)) {
					$content .= trim($line) . "\n";
				}
			}
			fclose($fd);
		}
		return $content;
	}


	/**
	 * This function returns the mime type of the file specified by the url
	 *
	 * @param	string		$url: the url
	 * @return	string		$mimeType: the mime type found in the header
	 */
	public function getMimeType($url) {
		$mimeType = '';
		$headers = trim(t3lib_div::getURL($url, 2));
		if ($headers) {
			$matches = array();
			if (preg_match('/(Content-Type:[\s]*)([a-zA-Z_0-9\/\-\.\+]*)([\s]|$)/', $headers, $matches)) {
				$mimeType = trim($matches[2]);
			}
		}
		return $mimeType;
	}


	/**
	 * Returns the absolute address of a link. This is based on
	 * $this->theParts["html"]["path"] being the root-address
	 *
	 * @param	string		$ref: address to use
	 * @return	string		the absolute address
	 */
	public function absRef($ref) {
		$ref = trim($ref);
		$info = parse_url($ref);
		if ($info['scheme']) {
			return $ref;
		} elseif (preg_match("/^\//i",$ref)) {
			$addr = parse_url($this->theParts['html']['path']);
			return  $addr['scheme'].'://'.$addr['host'].($addr['port']?':'.$addr['port']:'').$ref;
		} else {
			// If the reference is relative, the path is added, in order for us to fetch the content
			return $this->theParts['html']['path'] . $ref;
		}
	}


	/**
	 * Returns information about a file reference
	 *
	 * @param	string		$fileref: the file to use
	 * @return	array		path, filename, filebody, fileext
	 */
	public function split_fileref($fileref) {
		$info = array();
		if (preg_match("/(.*\/)(.*)$/", $fileref, $reg)) {
			$info['path'] = $reg[1];
			$info['file'] = $reg[2];
		} else	{
			$info['path'] = '';
			$info['file'] = $fileref;
		}
		$reg = '';
		if (preg_match("/(.*)\.([^\.]*$)/", $info['file'], $reg)) {
			$info['filebody'] = $reg[1];
			$info['fileext'] = strtolower($reg[2]);
			$info['realFileext'] = $reg[2];
		} else	{
			$info['filebody'] = $info['file'];
			$info['fileext'] = '';
		}
		return $info;
	}


	/**
	 * Returns an array with file or url-information
	 *
	 * @param	string		$path: url to check
	 * @return	array		information about the path / URL
	 */
	public function extParseUrl($path) {
		$res = parse_url($path);
		preg_match("/(.*/)([^/]*)$/", $res['path'], $reg);
		$res['filepath'] = $reg[1];
		$res['filename'] = $reg[2];
		return $res;
	}


	/**
	 * Creates a regular expression out of a list of tags
	 *
	 * @param	mixed		$tagArray: the list of tags (either as array or string if it is one tag)
	 * @return	string		the regular expression
	 */
	public function tag_regex($tags) {
		$tags = (!is_array($tags) ? array($tags) : $tags);
		$regexp = '';
		$c = count($tags);
		foreach($tags as $tag) {
			$c--;
			$regexp .= '<' . sql_regcase($tag) . "[[:space:]]" . (($c) ? '|' : '');
		}
		return $regexp;
	}


	/**
	 * This function analyzes a HTML tag
	 * If an attribute is empty (like OPTION) the value of that key is just empty. Check it with is_set();
	 *
	 * @param	string		$tag: is either like this "<TAG OPTION ATTRIB=VALUE>" or
	 * 				this " OPTION ATTRIB=VALUE>" which means you can omit the tag-name
	 * @return	array		array with attributes as keys in lower-case
	 */
	public function get_tag_attributes($tag) {
		$attributes = array();
		$tag = ltrim(preg_replace("/^<[^ ]*/i","",trim($tag)));
		$tagLen = strlen($tag);
		$safetyCounter = 100;
			// Find attribute
		while ($tag) {
			$value = '';
			$reg = explode("[[:space:]=>]",$tag,2);
			$attrib = $reg[0];

			$tag = ltrim(substr($tag,strlen($attrib),$tagLen));
			if (substr($tag,0,1) == '=') {
				$tag = ltrim(substr($tag,1,$tagLen));
				if (substr($tag,0,1) == '"') {
						// Quotes around the value
					$reg = explode('"',substr($tag,1,$tagLen),2);
					$tag = ltrim($reg[1]);
					$value = $reg[0];
				} else {
						// No quotes around value
					preg_replace("^([^[:space:]>]*)(.*)",$tag,$reg);
					$value = trim($reg[1]);
					$tag = ltrim($reg[2]);
					if (substr($tag,0,1) == '>') {
						$tag = '';
					}
				}
			}
			$attributes[strtolower($attrib)] = $value;
			$safetyCounter--;
			if ($safetyCounter < 0) break;
		}
		return $attributes;
	}


	/**
	 * Implementation of quoted-printable encode.
	 * This function was a duplicate of t3lib_div::quoted_printable, thus it's going to be removed.
	 * Deprecated since TYPO3 4.0
	 *
	 * @param	string		Content to encode
	 * @return	string		The QP encoded string
	 * @deprecated since TYPO3 4.0, remove in TYPO 4.3
	 */
	public function quoted_printable($string) {
		return t3lib_div::quoted_printable($string, 76);
	}


	/**
	 * Converts a name field
	 * Deprecated since TYPO3 4.0
	 *
	 * @param	string		$name: the name
	 * @return	string		the name
	 * @deprecated since TYPO3 4.0, remove in TYPO3 4.3
	 */
	public function convertName($name) {
		return $name;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_htmlmail.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_htmlmail.php']);
}
?>
