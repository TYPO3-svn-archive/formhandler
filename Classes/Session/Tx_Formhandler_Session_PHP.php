<?php

class Tx_Formhandler_Session_PHP extends Tx_Formhandler_AbstractSession {

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