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
 * This helper overrides the hidden fields of the default form-helper and
 * takes the formValuesPrefix of formhandler into account
 * 
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_Formhandler_Fluid_ViewHelper_TranslateViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{
	/**
	 * @var array The latest language files from controller via view :/
	 */
	protected static $langFiles = array();
	
	/**
	 * Translate a given key or use the tag body as default.
	 *
	 * @param string $key The locallang key
	 * @param string $default if the given locallang key could not be found, this value is used. . If this argument is not set, child nodes will be used to render the default
	 * @param boolean $htmlEscape TRUE if the result should be htmlescaped. This won't have an effect for the default value
	 * @param array $arguments Arguments to be replaced in the resulting string
	 * @return string The translated key or tag body if key doesn't exist
	 */
	public function render($key, $default = NULL, $htmlEscape = TRUE, array $arguments = NULL)
	{
		foreach(self::$langFiles as $langFile)
		{
			$temp = trim($GLOBALS['TSFE']->sL('LLL:' . $langFile . ':' . $key));
			if(strlen($temp) > 0) {
				$message = $temp;
				break;
			} 
		}
		
		if (!strlen($message)) {
			$message = $default !== NULL ? $default : $this->renderChildren();
		} elseif ($htmlEscape) {
			$message = htmlspecialchars($message);
		}
		
		return is_array($arguments) ? vsprintf($message, $arguments) : $message;
	}
	
	/**
	 * Set the current language files - called from Tx_Formhandler_View_Fluid
	 * @see Tx_Formhandler_View_Fluid#setLangFiles()
	 * 
	 * @param array $langFiles
	 * @todo Think about a better way to share language settings
	 */
	public static function setLangFiles(array $langFiles)
	{
		self::$langFiles = array_reverse($langFiles);
	}
}