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
 * This finisher clears the cache. 
 * If no further configuration is set the current page's cache will be cleared.
 * Alternativly the cacheCmd can be set:
 * 
 * Example configuration:
 *
 * <code>
 * finishers.1.class = Tx_Formhandler_Finisher_ClearCache
 *
 * # The cache of page 15 will be cleared 
 * finishers.1.config.cacheCmd = 15
 * 
 * # cObject is supported...
 * finishers.1.config.cacheCmd = TEXT
 * finishers.1.config.cacheCmd.data = GP:someparameter
 * 
 * # for other cacheCmds see phpdoc in t3lib_TCEmain->clear_cacheCmd()
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
require_once('t3lib/class.t3lib_tcemain.php');
class Tx_Formhandler_Finisher_ClearCache extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$cacheCmd = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'cacheCmd');
		if (empty($cacheCmd)) {
			$cacheCmd = $GLOBALS['TSFE']->id;
		}

		Tx_Formhandler_StaticFuncs::debugMessage('cacheCmd', array($cacheCmd));

		$tce = t3lib_div::makeInstance('t3lib_tcemain');
		$tce->clear_cacheCmd($cacheCmd);
		return $this->gp;
	}
}
?>
