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
*                                                                        */

/**
 * A session class for Formhandler using PHP sessions
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_Session_PHP extends Tx_Formhandler_AbstractSession {

	/* (non-PHPdoc)
	 * @see Classes/Component/Tx_Formhandler_AbstractClass#__construct()
	*/
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, 
								Tx_Formhandler_Configuration $configuration, 
								Tx_Formhandler_Globals $globals,
								Tx_Formhandler_UtilityFuncs $utilityFuncs) {

		parent::__construct($componentManager, $configuration, $globals, $utilityFuncs);
		$this->start();
		$threshold = $this->utilityFuncs->getTimestamp(1, 'hours');
		if($this->settings['clearOldSession.']['value']) {
			$threshold = $this->utilityFuncs->getTimestamp($this->settings['clearOldSession.']['value'], $this->settings['clearOldSession.']['unit']);
		}
		if(is_array($_SESSION['formhandler'])) {
			foreach($_SESSION['formhandler'] as $hashedID => $sesData) {
				if($this->globals->getFormValuesPrefix() === $sesData['formValuesPrefix'] && $sesData['creationTstamp'] < $threshold) {
					unset($_SESSION['formhandler'][$hashedID]);
				}
			}
		} else {
			$_SESSION['formhandler'] = array();
		}
	}

	/* (non-PHPdoc)
	 * @see Classes/Session/Tx_Formhandler_AbstractSession#set()
	*/
	public function set($key, $value) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		$data[$this->globals->getRandomID()][$key] = $value;
		$_SESSION['formhandler'] = $data;
	}

	/* (non-PHPdoc)
	 * @see Classes/Session/Tx_Formhandler_AbstractSession#setMultiple()
	*/
	public function setMultiple($values) {
		if(is_array($values) && !empty($values)) {
			$this->start();
			$data = $_SESSION['formhandler'];
			if (!is_array($data[$this->globals->getRandomID()])) {
				$data[$this->globals->getRandomID()] = array();
			}
			foreach($values as $key => $value) {
				$data[$this->globals->getRandomID()][$key] = $value;
			}
			$_SESSION['formhandler'] = $data;
		}
	}

	/* (non-PHPdoc)
	 * @see Classes/Session/Tx_Formhandler_AbstractSession#get()
	*/
	public function get($key) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		return $data[$this->globals->getRandomID()][$key];
	}

	/* (non-PHPdoc)
	 * @see Classes/Session/Tx_Formhandler_AbstractSession#exists()
	*/
	public function exists() {
		$this->start();
		$data = $_SESSION['formhandler'];
		return is_array($data[$this->globals->getRandomID()]);
	}

	/* (non-PHPdoc)
	 * @see Classes/Session/Tx_Formhandler_AbstractSession#reset()
	*/
	public function reset() {
		$this->start();
		unset($_SESSION['formhandler'][$this->globals->getRandomID()]);
	}

}

?>