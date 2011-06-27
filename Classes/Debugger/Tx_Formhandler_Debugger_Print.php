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
 * $Id: Tx_Formhandler_AbstractLogger.php 27708 2009-12-15 09:22:07Z reinhardfuehricht $
 *                                                                        */

/**
 * A simple debugger printing the messages on the screen
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Debugger
 */
class Tx_Formhandler_Debugger_Print extends Tx_Formhandler_AbstractDebugger {

	public function outputDebugLog() {
		$out = '';

		foreach($this->debugLog as $section => $logData) {
			$out .= $this->globals->getCObj()->wrap($section, $this->settings['sectionHeaderWrap']);
			$sectionContent = '';
			foreach($logData as $messageData) {
				$message = str_replace("\n", '<br />', $messageData['message']);
				$message = $this->globals->getCObj()->wrap($message, $this->settings['severityWrap.'][$messageData['severity']]);
				$sectionContent .= $this->globals->getCObj()->wrap($message, $this->settings['messageWrap']);
				if($messageData['data']) {
					if (t3lib_div::int_from_ver(TYPO3_branch) < t3lib_div::int_from_ver('4.5')) {
						$sectionContent .= t3lib_div::view_array($messageData['data']);
					} else {
						$sectionContent .= t3lib_utility_Debug::viewArray($messageData['data']);
					}
					$sectionContent .= '<br />';
				}
			}
			$out .= $this->globals->getCObj()->wrap($sectionContent, $this->settings['sectionWrap']);
		}
		print $out;
	}

	public function validateConfig() {
		if(!$this->settings['sectionWrap']) {
			$this->settings['sectionWrap'] = '<div style="border:1px solid #ccc; padding:7px; background:#dedede;">|</div>';
		}
		if(!$this->settings['sectionHeaderWrap']) {
			$this->settings['sectionHeaderWrap'] = '<h2 style="background:#333; color:#cdcdcd;height:23px;padding:10px 7px 7px 7px;margin:0;">|</h2>';
		}
		if(!$this->settings['messageWrap']) {
			$this->settings['messageWrap'] = '<div style="font-weight:bold;">|</div>';
		}
		if(!$this->settings['severityWrap.']['1']) {
			$this->settings['severityWrap.']['1'] = '<span style="color:#000;">|</span>';
		}
		if(!$this->settings['severityWrap.']['2']) {
			$this->settings['severityWrap.']['2'] = '<span style="color:#FF8C00;">|</span>';
		}
		if(!$this->settings['severityWrap.']['3']) {
			$this->settings['severityWrap.']['3'] = '<span style="color:#FF2800;">|</span>';
		}
	}

}

?>