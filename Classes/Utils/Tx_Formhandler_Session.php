<?php

class Tx_Formhandler_Session {
	
	static public function set($key, $value) {
		session_start();
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if(!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		$data[Tx_Formhandler_Globals::$randomID][$key] = $value;

		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		
	}
	
	static public function get($key) {
		session_start();
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if(!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		
		return $data[Tx_Formhandler_Globals::$randomID][$key];
	}
	
	static public function sessionExists() {
		session_start();
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		
		return is_array($data[Tx_Formhandler_Globals::$randomID]);
	}
	
	static public function reset() {
		session_start();
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', array());
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}
}

?>