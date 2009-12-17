<?php

class Tx_Formhandler_Generator_Csv extends Tx_Formhandler_AbstractGenerator {
	
	/**
	 * Renders the CSV.
	 *
	 * @return void
	 */
	public function process() {
		$params = $this->gp;
		//require class for $this->csv
		require_once('typo3conf/ext/formhandler/Resources/PHP/csv.lib.php');

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

		//init csv object
		$this->csv = new export2CSV(',', "\n");
		$data[0] = $params;

		//generate file
		$this->csv = $this->csv->create_csv_file($data);
		header('Content-type: application/eml');
		header('Content-Disposition: attachment; filename=formhandler.csv');
		echo $this->csv;
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