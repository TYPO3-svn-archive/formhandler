<?php

abstract class Tx_Formhandler_AbstractSession {

	protected $started = FALSE;

	protected function start() {
		if (!$this->started) {
			$current_session_id = session_id();
			if (empty($current_session_id)) {
				session_start();
			}
			$this->started = TRUE;
		}
	}

	abstract public function set($key, $value);

	abstract public function get($key);

	abstract public function exists();

	abstract public function reset();

}

?>