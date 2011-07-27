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
 * $Id: Tx_Formhandler_Interceptor_Default.php 27708 2009-12-15 09:22:07Z reinhardfuehricht $
 *                                                                        */

/**
 * Protects the form against CSRF using the TYPO3 form protection API.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Interceptor
 */

require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.tx_formhandler_formprotection.php');

class Tx_Formhandler_Interceptor_FormProtection extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		try {

			$formName = $this->globals->getRandomID();

			//If the form is submitted with a form token, validate the token
			if($this->globals->isSubmitted() && $this->gp['formToken']) {
				$isValid = t3lib_formprotection_factory::get('tx_formhandler_formprotection')->validateToken($this->gp['formToken'], $formName);
				if(!$isValid) {
					$this->utilityFuncs->throwException('formprotection_invalid_token');
				}

				//A new token is needed, since the current was already used
				$this->generateToken($formName);

			//The form was submitted without a form token. Throw exception and exit.
			} elseif($this->globals->isSubmitted()) {
				$this->utilityFuncs->throwException('formprotection_no_token');

			//The form was not submitted, generate a new token.
			} else {
				$this->generateToken($formName);
			}
		} catch(Exception $e) {
			$redirectPage = $this->utilityFuncs->getSingle($this->settings, 'redirectPage');
			if($redirectPage) {
				$this->utilityFuncs->doRedirect($redirectPage, $this->settings['correctRedirectUrl'], $this->settings['additionalParams.']);
			} else {
				throw new Exception($e->getMessage());
			}
		}
		return $this->gp;
	}

	protected function generateToken($formName) {
		$this->gp['formToken'] = t3lib_formprotection_factory::get('tx_formhandler_formprotection')->generateToken($formName);
	}

}
?>