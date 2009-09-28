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
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		if($prefix) {
			$linkGP[$prefix]['action'] = 'csv';
		} else {
			$linkGP['action'] = 'csv';
		}
		
		return $this->cObj->getTypolink('CSV', $GLOBALS['TSFE']->id, $linkGP);
	}
}

?>