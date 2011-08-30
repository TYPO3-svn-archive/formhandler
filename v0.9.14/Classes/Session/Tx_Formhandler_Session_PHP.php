<?php

class Tx_Formhandler_Session_PHP extends Tx_Formhandler_AbstractSession {

	public function set($key, $value) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		$data[Tx_Formhandler_Globals::$randomID][$key] = $value;
		$_SESSION['formhandler'] = $data;
	}
	
	public function setMultiple($values) {
		if(is_array($values) && !empty($values)) {
			$this->start();
			$data = $_SESSION['formhandler'];
			if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
				$data[Tx_Formhandler_Globals::$randomID] = array();
			}
			
			foreach($values as $key => $value) {
				$data[Tx_Formhandler_Globals::$randomID][$key] = $value;
			}
			$_SESSION['formhandler'] = $data;
		}
	}

	public function get($key) {
		$this->start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		return $data[Tx_Formhandler_Globals::$randomID][$key];
	}

	public function exists() {
		$this->start();
		$data = $_SESSION['formhandler'];
		return is_array($data[Tx_Formhandler_Globals::$randomID]);
	}

	public function reset() {
		$this->start();
		unset($_SESSION['formhandler'][Tx_Formhandler_Globals::$randomID]);
	}

}

?>