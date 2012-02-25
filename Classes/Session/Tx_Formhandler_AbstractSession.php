<?php
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
*                                                                        *
* TYPO3 is free software; you can redistribute it and/or modify it under *
* the terms of the GNU General Public License version 2 as published by  *
* the Free Software Foundation.                                          *
*                                                                        *
* This script is distributed in the hope that it will be useful, but     *
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
* TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
* Public License for more details.                                       *
*                                                                        */

/**
 * An abstract session class for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
abstract class Tx_Formhandler_AbstractSession extends Tx_Formhandler_AbstractClass {

	/**
	 * An indicator if a session was already started
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $started = FALSE;

	/**
	 * Starts a new PHP session
	 *
	 * @return void
	 */
	protected function start() {
		if (!$this->started) {
			$current_session_id = session_id();
			if (empty($current_session_id)) {
				session_start();
			}
			$this->started = TRUE;
		}
	}

	/**
	 * Sets a key
	 *
	 * @param string $key The key
	 * @param string $value The value to set
	 * @return void
	 */
	abstract public function set($key, $value);

	/**
	 * Sets multiple keys at once
	 *
	 * @param array $values key value pairs
	 * @return void
	 */
	abstract public function setMultiple($values);

	/**
	 * Get the value of the given key
	 *
	 * @param string $key The key
	 * @return string The value
	 */
	abstract public function get($key);

	/**
	 * Checks if a session exists
	 *
	 * @return boolean
	 */
	abstract public function exists();

	/**
	 * Resets all session values
	 *
	 * @return void
	 */
	abstract public function reset();
	
	protected function getOldSessionThreshold() {
		$threshold = $this->utilityFuncs->getTimestamp(1, 'hours');
		if($this->settings['clearSessionsOlderThan.']['value']) {
			$thresholdValue = $this->utilityFuncs->getSingle($this->settings['clearSessionsOlderThan.'], 'value');
			$thresholdUnit = $this->utilityFuncs->getSingle($this->settings['clearSessionsOlderThan.'], 'unit');
			$threshold = $this->utilityFuncs->getTimestamp($thresholdValue, $thresholdUnit);
		}
		return $threshold;
	}

}

?>