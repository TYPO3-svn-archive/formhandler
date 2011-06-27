<?php

class Tx_Formhandler_Session_TYPO3 extends Tx_Formhandler_AbstractSession {

	public function set($key, $value) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		$data[$this->globals->getRandomID()][$key] = $value;
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	public function setMultiple($values) {
		if(is_array($values) && !empty($values)) {
			$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
			if (!is_array($data[$this->globals->getRandomID()])) {
				$data[$this->globals->getRandomID()] = array();
			}

			foreach($values as $key => $value) {
				$data[$this->globals->getRandomID()][$key] = $value;
			}

			$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

	public function get($key) {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		if (!is_array($data[$this->globals->getRandomID()])) {
			$data[$this->globals->getRandomID()] = array();
		}
		return $data[$this->globals->getRandomID()][$key];
	}

	public function exists() {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		return is_array($data[$this->globals->getRandomID()]);
	}

	public function reset() {
		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		unset($data[$this->globals->getRandomID()]);
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

}

?>