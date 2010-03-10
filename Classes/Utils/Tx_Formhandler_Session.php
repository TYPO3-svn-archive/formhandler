<?php

class Tx_Formhandler_Session {
	
	static public function set($key, $value) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		$cObj = Tx_Formhandler_Globals::$cObj;
		if(!is_array($data[$cObj->data['uid']])) {
			$data[$cObj->data['uid']] = array();
		}
		$data[$cObj->data['uid']][$key] = $value;

		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		
	}
	
	static public function get($key) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		$cObj = Tx_Formhandler_Globals::$cObj;
		if(!is_array($data[$cObj->data['uid']])) {
			$data[$cObj->data['uid']] = array();
		}
		
		return $data[$cObj->data['uid']][$key];
	}
	
	static public function reset() {
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', array());
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}
}

?>