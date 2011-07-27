<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011 Reinhard Führicht <rf@typoheads.at>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class tx_formhandler_formprotection.
 *
 * This class is a dummy implementation of the form protection,
 * which is used when no authentication is used.
 *
 * $Id$
 *
 * @package TYPO3
 * @subpackage Formhandler
 */
class tx_formhandler_formprotection extends t3lib_formprotection_Abstract {

	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
	 */
	protected $componentManager;

	/**
	 * The global Formhandler values
	 *
	 * @access protected
	 * @var Tx_Formhandler_Globals
	 */
	protected $globals;

	public function __construct() {
		$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
		$this->globals = $this->componentManager->getComponent('Tx_Formhandler_Globals');

	}

	/**
	 * Generates a token for a form by hashing the given parameters
	 * with the secret session token.
	 *
	 * Calling this function two times with the same parameters will create
	 * the same valid token during one user session.
	 *
	 * @param string $formName
	 *		the name of the form, for example a table name like "tt_content",
	 *		or some other identifier like "install_tool_password", must not be
	 *		empty
	 * @param string $action
	 *		the name of the action of the form, for example "new", "delete" or
	 *		"edit", may also be empty
	 * @param string $formInstanceName
	 *		a string used to differentiate two instances of the same form,
	 *		form example a record UID or a comma-separated list of UIDs,
	 *		may also be empty
	 *
	 * @return string the 32-character hex ID of the generated token
	 */
	public function generateToken($formName, $action = '', $formInstanceName = '') {

		//Generate a new random session token each time a new form token is generated
		$this->globals->getSession()->set('formProtectionSessionToken', NULL);
		$this->retrieveSessionToken();

		$tokenId = t3lib_div::hmac(
			(string)$formName .
			(string)$action .
			(string)$formInstanceName .
			$this->sessionToken
		);

		return $tokenId;
	}

	/**
	 * Checks whether the token $tokenId is valid in the form $formName with
	 * $formInstanceName.
	 *
	 * @param string $tokenId
	 *		a form token to check, may also be empty or utterly malformed
	 * @param string $formName
	 *		the name of the form to check, for example "tt_content",
	 *		may also be empty or utterly malformed
	 * @param string $action
	 *		the action of the form to check, for example "edit",
	 *		may also be empty or utterly malformed
	 * @param string $formInstanceName
	 *		the instance name of the form to check, for example "42" or "foo"
	 *		or "31,42", may also be empty or utterly malformed
	 *
	 * @return boolean
	 *		 TRUE if $tokenId, $formName, $action and $formInstanceName match
	 */
	public function validateToken($tokenId, $formName, $action = '', $formInstanceName = '') {

		//Load token from session into $this->sessionToken
		$this->retrieveSessionToken();

		$validTokenId = t3lib_div::hmac(
			(string)$formName .
			(string)$action .
			(string)$formInstanceName .
			$this->sessionToken
		);

		if ((string)$tokenId === $validTokenId) {
			$isValid = TRUE;
		} else {
			$isValid = FALSE;
		}

		return $isValid;
	}

	/**
	 * Creates or displays an error message telling the user that the submitted
	 * form token is invalid.
	 *
	 * @return void
	 */
	protected function createValidationErrorMessage() {
		//Do nothing.
	}

	/**
	 * Generates the random token which is used in the hash for the form tokens.
	 *
	 * @return string
	 */
	protected function generateSessionToken() {
		return bin2hex(t3lib_div::generateRandomBytes(32));
	}

	/**
	 * Retrieves the saved session token or generates a new one.
	 *
	 * @return array<array>
	 *		 the saved tokens as, will be empty if no tokens have been saved
	 */
	protected function retrieveSessionToken() {
		$this->sessionToken = $this->globals->getSession()->get('formProtectionSessionToken');
		if (empty($this->sessionToken)) {
			$this->sessionToken = $this->generateSessionToken();
			$this->persistSessionToken();
		}
	}

	/**
	 * Saves the tokens so that they can be used by a later incarnation of this
	 * class.
	 *
	 * @return void
	 */
	public function persistSessionToken() {
		$this->globals->getSession()->set('formProtectionSessionToken', $this->sessionToken);
	}
}

?>