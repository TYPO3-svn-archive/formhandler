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
 * Class to generate CSV files in Backend
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @uses export2CSV in csv.lib.php
 */
require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/parsecsv.lib.php');
class Tx_Formhandler_Generator_CSV {

	/**
	 * The internal CSV object
	 *
	 * @access protected
	 * @var export2CSV
	 */
	protected $csv;

	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
	 */
	protected $componentManager;

	/**
	 * Default Constructor
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager The component manager of Formhandler
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager) {
		$this->componentManager = $componentManager;
	}

	/**
	 * Function to generate a CSV file from submitted form values. This function is called by Tx_Formhandler_Controller_Backend
	 *
	 * @param array $records The records to export to CSV
	 * @param array $exportParams A list of fields to export. If not set all fields are exported
	 * @see Tx_Formhandler_Controller_Backend::generateCSV()
	 * @return void
	 */
	public function generateModuleCSV($records, $exportParams = array(), $delimiter = ',', $enclosure = '"', $encoding = 'UTF-8') {

		$data = array();
		$dataSorted = array();

		//build data array
		foreach ($records as $idx => $record) {
			if (!is_array($record['params'])) {
				$record['params'] = array();
			}
			foreach ($record['params'] as $subIdx => &$param) {
				if (is_array($param)) {
					$param = implode(';', $param);
				}
			}
			if (count($exportParams) == 0 || in_array('pid', $exportParams)) {
				$record['params']['pid'] = $record['pid'];
			}
			if (count($exportParams) == 0 || in_array('submission_date', $exportParams)) {
				$record['params']['submission_date'] = date('d.m.Y H:i:s', $record['crdate']);
			}
			if (count($exportParams) == 0 || in_array('ip', $exportParams)) {
				$record['params']['ip'] = $record['ip'];
			}
			$data[] = $record['params'];
		}
		if (count($exportParams) > 0) {
			foreach ($data as $idx => &$params) {

				// fill missing fields with empty value
				foreach ($exportParams as $key => $exportParam) {
					if (!array_key_exists($exportParam, $params)) {
						$params[$exportParam] = '';
					}
				}

				// remove unwanted fields
				foreach ($params as $key => $value) {
					if (!in_array($key, $exportParams)) {
						unset($params[$key]);
					}
				}
			}
		}

		// sort data
		$dataSorted = array();
		foreach ($data as $idx => $array) {
			$dataSorted[] = $this->sortArrayByArray($array, $exportParams);
		}
		$data = $dataSorted;

		// create new parseCSV object.
		$csv = new parseCSV();
		$csv->delimiter = $csv->output_delimiter = $delimiter;
		$csv->enclosure = $enclosure;
		$csv->input_encoding = $this->getInputCharset();
		$csv->output_encoding = $encoding;
		$csv->convert_encoding = FALSE;
		if($csv->input_encoding !== $csv->output_encoding) {
			$csv->convert_encoding = TRUE;
		}
		$csv->output('formhandler.csv', $data, $exportParams);
		die();
	}

	/**
	 * Sorts the CSV data
	 *
	 * @return array The sorted array
	 */
	private function sortArrayByArray($array, $orderArray) {
		$ordered = array();
		foreach ($orderArray as $idx => $key) {
			if (array_key_exists($key, $array)) {
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}
	
	/**
	* Get charset used by TYPO3
	*
	* @return string Charset
	*/
	private function getInputCharset() {
		if (is_object($GLOBALS['LANG']) && $GLOBALS['LANG']->charSet) {
			$charset = $GLOBALS['LANG']->charSet;
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
			$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
		} else	{
			$charset = 'utf-8';
		}
		return $charset;
		}
}
?>
