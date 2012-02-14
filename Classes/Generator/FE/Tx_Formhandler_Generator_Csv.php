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
* CSV generator class for Formhandler
*
* @author	Reinhard Führicht <rf@typoheads.at>
*/
require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/parsecsv.lib.php');
class Tx_Formhandler_Generator_Csv extends Tx_Formhandler_AbstractGenerator {

	/**
	 * Renders the CSV.
	 *
	 * @return void
	 */
	public function process() {
		$params = $this->gp;
		$exportParams = $this->utilityFuncs->getSingle($this->settings, 'exportParams');
		if (!is_array($exportParams) && strpos($exportParams, ',') !== FALSE) {
			$exportParams = t3lib_div::trimExplode(',', $exportParams);
		}

		//build data
		foreach ($params as $key => &$value) {
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			if (!empty($exportParams) && !in_array($key, $exportParams)) {
				unset($params[$key]);
			}
			$value = str_replace('"', '""', $value);
		}

		// create new parseCSV object.
		$csv = new parseCSV();

		//parseCSV expects data to be a two dimensional array
		$data = array($params);

		$fields = FALSE;
		if(intval($this->utilityFuncs->getSingle($this->settings, 'addFieldNames')) === 1) {
			$fields = array_keys($params);
			$csv->heading = TRUE;
		}

		if($this->settings['delimiter']) {
			$csv->delimiter = $csv->output_delimiter = $this->utilityFuncs->getSingle($this->settings, 'delimiter');
		}
		if($this->settings['enclosure']) {
			$csv->enclosure = $this->utilityFuncs->getSingle($this->settings, 'enclosure');
		}
		if(intval($this->settings['returnFileName']) === 1) {
			$outputPath = $this->utilityFuncs->getDocumentRoot();
			if ($this->settings['customTempOutputPath']) {
				$outputPath .= $this->utilityFuncs->sanitizePath($this->settings['customTempOutputPath']);
			} else {
				$outputPath .= '/typo3temp/';
			}
			$filename = $outputPath . $this->settings['filePrefix'] . $this->utilityFuncs->generateHash() . '.csv';
			$csv->save($filename, $data, FALSE, $fields);

			return $filename;
		} else {
			$csv->output('formhandler.csv', $data, $fields);
			die();
		}
	}

	/* (non-PHPdoc)
	 * @see Classes/Generator/Tx_Formhandler_AbstractGenerator#getComponentLinkParams($linkGP)
	*/
	protected function getComponentLinkParams($linkGP) {
		$prefix = $this->globals->getFormValuesPrefix();
		$tempParams = array(
			'action' => 'csv'
		);
		$params = array();
		if ($prefix) {
			$params[$prefix] = $tempParams;
		} else {
			$params = $tempParams;
		}
		return $params;
	}

}

?>