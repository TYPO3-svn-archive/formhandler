<?php

class Tx_Formhandler_Session {

	static protected $started = FALSE;

	static protected function start() {

		if (!self::$started) {
			$current_session_id = session_id();
			if (empty($current_session_id)) {
				session_start();
			}
			self::$started = TRUE;
		}
	}

	static public function set($key, $value) {
		self::start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		$data[Tx_Formhandler_Globals::$randomID][$key] = $value;
		$_SESSION['formhandler'] = $data;
	}

	static public function get($key) {
		self::start();
		$data = $_SESSION['formhandler'];
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		return $data[Tx_Formhandler_Globals::$randomID][$key];
	}

	static public function sessionExists() {
		self::start();
		$data = $data = $_SESSION['formhandler'];
		return is_array($data[Tx_Formhandler_Globals::$randomID]);
	}

	static public function reset() {
		self::start();
		unset($_SESSION['formhandler'][Tx_Formhandler_Globals::$randomID]);
	}
}

?>