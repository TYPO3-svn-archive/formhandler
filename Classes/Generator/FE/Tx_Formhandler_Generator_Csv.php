<?php

class Tx_Formhandler_Generator_Csv extends Tx_Formhandler_AbstractGenerator {
	
	/**
	 * Renders the CSV.
	 *
	 * @return void
	 */
	public function process() {
		$params = $this->gp;
		require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/parsecsv.lib.php');

		//build data
		foreach($params as $key => &$value) {
			if(is_array($value)) {
				$value = implode(',', $value);
			}
			if(count($exportParams) > 0 && !in_array($key, $exportParams)) {
				unset($params[$key]);
			}
			$value = str_replace('"', '""', $value);
		}

		// create new parseCSV object.
		$csv = new parseCSV();
		$csv->output('formhandler.csv', $data, $exportParams);
		die();
	}
	
	public function getLink($linkGP) {
		$linkGP = array();
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		if($prefix) {
			$linkGP[$prefix]['action'] = 'csv';
			$linkGP[$prefix]['submitted'] = '1';
			$linkGP[$prefix]['submitted_ok'] = '1';
		} else {
			$linkGP['action'] = 'csv';
			$linkGP['submitted'] = '1';
			$linkGP['submitted_ok'] = '1';
		}
		$linkGP['no_cache'] = 1;
		$linkGP['dontReset'] = 1;
		return $this->cObj->getTypolink('CSV', $GLOBALS['TSFE']->id, $linkGP);
	}
}

?>