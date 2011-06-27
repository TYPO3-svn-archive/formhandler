<?php

class Tx_Formhandler_Session_PHP extends Tx_Formhandler_AbstractSession {
	
	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
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
	 * The global Formhandler values
	 *
	 * @access protected
	 * @var Tx_Formhandler_Globals
	 */
	protected $globals;

	/**
	 * The Formhandler utility methods
	 *
	 * @access protected
	 * @var Tx_Formhandler_UtilityFuncs
	 */
	protected $utlityFuncs;

	public function set($key, $value) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		$data[$this->globals->getRandomID()][$key] = $value;
		$_SESSION['formhandler'] = $data;
	}
	
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

	public function get($key) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		return $data[$this->globals->getRandomID()][$key];
	}

	public function exists() {
		$this->start();
		$data = $_SESSION['formhandler'];
		return is_array($data[$this->globals->getRandomID()]);
	}

	public function reset() {
		$this->start();
		unset($_SESSION['formhandler'][$this->globals->getRandomID()]);
	}

}

?>