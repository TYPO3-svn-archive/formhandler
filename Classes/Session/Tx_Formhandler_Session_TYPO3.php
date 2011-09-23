<?php

class Tx_Formhandler_Session_TYPO3 extends Tx_Formhandler_AbstractSession {

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

		$data = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler');
		foreach($data as $hashedID => $sesData) {
			if($this->globals->getFormValuesPrefix() === $sesData['formValuesPrefix'] && $sesData['creationTstamp'] < $threshold) {
				unset($data[$hashedID]);
			}
		}

		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler', $data);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

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