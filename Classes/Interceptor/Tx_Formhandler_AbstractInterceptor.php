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
 * @abstract
 */
abstract class Tx_Formhandler_AbstractInterceptor extends Tx_Formhandler_AbstractComponent {

	/**
	 * Logs an action of an interceptor, e.g. if Interceptor_IPBlocking blocked a request.
	 *
	 * @param boolean $markAsSpam Indicates if this was a blocked SPAM attempt. Will be highlighted in the backend module.
	 * @return void
	 */
	protected function log($markAsSpam = FALSE) {
		$classesArray = $this->settings['loggers.'];
		if (isset($classesArray) && is_array($classesArray)) {
			foreach ($classesArray as $idx => $tsConfig) {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class']) && intval($this->utilityFuncs->getSingle($tsConfig, 'disable')) !== 1) {
					$className = $this->utilityFuncs->prepareClassName($tsConfig['class']);
					$this->utilityFuncs->Message('calling_class', array($className));
					$obj = $this->componentManager->getComponent($className);
					if ($markAsSpam) {
						$tsConfig['config.']['markAsSpam'] = 1;
					}
					$obj->init($this->gp, $tsConfig['config.']);
					$obj->process();
				} else {
					$this->utilityFuncs->throwException('classesarray_error');
				}
			}
		}
	}

}
?>
