<?php

class Tx_Formhandler_Session_TYPO3 extends Tx_Formhandler_AbstractSession {

	public function set($key, $value) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		$data[Tx_Formhandler_Globals::$randomID][$key] = $value;
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	public function setMultiple($values) {
		if(is_array($values) && !empty($values)) {
			$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
			if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
				$data[Tx_Formhandler_Globals::$randomID] = array();
			}

			foreach($values as $key => $value) {
				$data[Tx_Formhandler_Globals::$randomID][$key] = $value;
			}

			$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

	public function get($key) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if (!is_array($data[Tx_Formhandler_Globals::$randomID])) {
			$data[Tx_Formhandler_Globals::$randomID] = array();
		}
		return $data[Tx_Formhandler_Globals::$randomID][$key];
	}

	public function exists() {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		return is_array($data[Tx_Formhandler_Globals::$randomID]);
	}

	public function reset() {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		unset($data[Tx_Formhandler_Globals::$randomID]);
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

}

?>