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
 * A class providing messages for exceptions and debugging
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Utils
 */
class Tx_Formhandler_Messages {

	/**
	 * Returns a debug message according to given key
	 *
	 * @param string The key in translation file
	 * @return string
	 */
	public static function getDebugMessage($key) {
		return trim($GLOBALS['TSFE']->sL('LLL:EXT:formhandler/Resources/Language/locallang_debug.xml:' . $key));
	}

	/**
	 * Returns an exception message according to given key
	 *
	 * @param string The key in translation file
	 * @return string
	 */
	public static function getExceptionMessage($key) {
		return trim($GLOBALS['TSFE']->sL('LLL:EXT:formhandler/Resources/Language/locallang_exceptions.xml:' . $key));
	}

	/**
	 * Returns a formatted debug message
	 *
	 * @param string The unformatted message
	 * @return string The formatted message
	 */
	public static function formatDebugMessage($message) {
		$message = str_replace("\n", '<br />', $message);
		return '<div style="font-weight:bold;color:#333;">' . $message . '</div>' . "\n";	
	}

	/**
	 * Returns a formatted debug section header
	 *
	 * @param string The unformatted message
	 * @return string The formatted message
	 */
	public static function formatDebugHeader($message) {
		return '<h2 style="background:#333; color:#cdcdcd;height:23px;padding:10px 7px 7px 7px;margin:0;">' . $message . '</h2>' . "\n";	
	}

}

?>
