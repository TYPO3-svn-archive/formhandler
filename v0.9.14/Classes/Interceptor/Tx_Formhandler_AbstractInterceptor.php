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
 * Abstract interceptor class
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Interceptor
 * @abstract
 */
abstract class Tx_Formhandler_AbstractInterceptor extends Tx_Formhandler_AbstractComponent {

	protected function log($markAsSpam = FALSE) {
		$classesArray = $this->settings['loggers.'];
		if (isset($classesArray) && is_array($classesArray)) {
			foreach ($classesArray as $idx => $tsConfig) {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class']) && intval($tsConfig['disable']) !== 1) {
					$className = Tx_Formhandler_StaticFuncs::prepareClassName($tsConfig['class']);
					Tx_Formhandler_StaticFuncs::Message('calling_class', array($className));
					$obj = $this->componentManager->getComponent($className);
					if ($markAsSpam) {
						$tsConfig['config.']['markAsSpam'] = 1;
					}
					$obj->init($this->gp, $tsConfig['config.']);
					$obj->process();
				} else {
					Tx_Formhandler_StaticFuncs::throwException('classesarray_error');
				}
			}
		}
	}

}
?>
