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
 *
 * $Id: Tx_Formhandler_Finisher_ClearCache.php 22614 2009-07-21 20:43:47Z fabien_u $
 *                                                                        */

/**
 * This finisher generates a unique code for a database entry.
 * This can be used for FE user registration or newsletter registration.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_GenerateAuthCode extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		$table = $this->gp['saveDB']['table'];
		$uid = $this->gp['saveDB']['uid'];
		if($table && $uid) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid=' . $uid);
			if($res) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$authCode = md5(serialize($row));
				$this->gp['generated_authCode'] = $authCode;
			}
		}
		return $this->gp;
	}
}
?>
