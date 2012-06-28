<?php
/***************************************************************
 *  Copyright notice
*
*  (c) 2010 Dev-Team Typoheads (dev@typoheads.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/



/**
 * UserFunc for rendering of log entry
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class tx_formhandler_tcafuncs {

	public function user_getParams($PA, $fobj) {
		$params = unserialize($PA['itemFormElValue']);
		$output =
			'<input
			readonly="readonly" style="display:none"
			name="' . $PA['itemFormElName'] . '"
			value="' . htmlspecialchars($PA['itemFormElValue']) . '"
			onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"
			' . $PA['onFocus'] . '/>
		';
		if (t3lib_div::int_from_ver(TYPO3_branch) < t3lib_div::int_from_ver('4.5')) {
			$output .= t3lib_div::view_array($params);
		} else {
			$output .= t3lib_utility_Debug::viewArray($params);
		}
		return $output;
	}

}
?>