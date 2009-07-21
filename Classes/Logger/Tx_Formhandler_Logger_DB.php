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
 * $Id$
 *                                                                        */

/**
 * A logger to store submission information in TYPO3 database
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Logger
 */
class Tx_Formhandler_Logger_DB {

	/**
	 * Logs the given values.
	 *
	 * @param array $gp The current GET/POST parameters
	 * @param array $settings The settings for the logger
	 * @return void
	 */
	public function log(&$gp, $settings) {

		//set params
		$table = "tx_formhandler_log";

		$fields['ip'] = t3lib_div::getIndpEnv('REMOTE_ADDR');
		if(isset($settings['disableIPlog']) && intval($settings['disableIPlog']) == 1) {
			$fields['ip'] = NULL;
		}
		$fields['tstamp'] = time();
		$fields['crdate'] = time();
		$fields['pid'] = $GLOBALS['TSFE']->id;
		$keys = array_keys($gp);
		ksort($gp);
		sort($keys);
		$serialized = serialize($gp);
		$hash = hash("md5",serialize($keys));
		$fields['params'] = $serialized;
		$fields['key_hash'] = $hash;
		
		if(intval($settings['markAsSpam']) == 1) {
			$fields['is_spam'] = 1;
		}

		#$fields = $GLOBALS['TYPO3_DB']->fullQuoteArray($fields,$table);

		//query the database
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fields);
		if(!$settings['nodebug']) {
			Tx_Formhandler_StaticFuncs::debugMessage('logging', $table, implode(',', $fields));
			if(strlen($GLOBALS['TYPO3_DB']->sql_error()) > 0) {
				Tx_Formhandler_StaticFuncs::debugMessage('error', $GLOBALS['TYPO3_DB']->sql_error());
			}
				
		}

	}

}
?>
