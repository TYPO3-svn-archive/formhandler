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
 * Abstract class for error checks for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	ErrorChecks
 */
abstract class Tx_Formhandler_AbstractErrorCheck {

	/**
	 * The GimmeFive component manager
	 *
	 * @access protected
	 * @var Tx_GimmeFive_Component_Manager
	 */
	protected $componentManager;

	/**
	 * The global Formhandler configuration
	 *
	 * @access protected
	 * @var Tx_Formhandler_Configuration
	 */
	protected $configuration;

	/**
	 * The GET/POST parameters
	 *
	 * @access protected
	 * @var array
	 */
	protected $gp;

	/**
	 * The cObj to render TypoScript objects
	 *
	 * @access protected
	 * @var array
	 */
	protected $cObj;

	/**
	 * The constructor for an interceptor setting the component manager and the configuration.
	 *
	 * @param Tx_GimmeFive_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_GimmeFive_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		if($GLOBALS['TSFE']->id) {
			$this->cObj = Tx_Formhandler_StaticFuncs::$cObj;
		}
	}

	/**
	 * Performs the specific error check.
	 *
	 * @param array &$check The TypoScript settings for this error check
	 * @param string $name The field name
	 * @param array &$gp The current GET/POST parameters
	 * @return string The error string
	 */
	abstract public function check(&$check, $name, &$gp);

	
	/**
	 * Sets the suitable string for the checkFailed message parsed in view.
	 *
	 * @param array $check The parsed check settings
	 * @return string The check failed string
	 */
	protected function getCheckFailed($check) {
		$checkFailed = $check['check'];
		if(is_array($check['params'])) {
			$checkFailed .= ';';
			foreach($check['params'] as $key => $value) {
				$checkFailed .= $key . '::' . $value . ';';
			}
			$checkFailed = substr($checkFailed, 0, (strlen($checkFailed) - 1));
		}
		return $checkFailed;
	}
	
	/**
	 * Parses the parameter given to the error check and performs getSingle if necessary.
	 *
	 * @param string $obj A value string or TypoScript object
	 * @param array $params If TypoScript object, this is the parameter array
	 * @return string The parsed value
	 */
	protected function getCheckValue($obj, $params) {
		$checkValue = $obj;
		if(is_array($params)) {
			$checkValue = $this->cObj->cObjGetSingle($obj, $params);
		}
		return $checkValue;
	}

}
?>
