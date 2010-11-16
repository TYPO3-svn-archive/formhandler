<?php

class Tx_Formhandler_Generator_PdfGenerator extends Tx_Formhandler_AbstractGenerator {

	/**
	 * Renders the PDF.
	 *
	 * @return void
	 */
	public function process() {
		
	}

	protected function getComponentLinkParams($linkGP) {
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		$type = 123;
		if ($this->settings['type']) {
			$type = $this->settings['type'];
		}
		$params = array();
		if ($prefix) {
			$params[$prefix] = array(
				'submitted_ok' => 1
			);
		} else {
			$params['submitted_ok'] = 1;
		}
		$params['type'] = $type;
		return $params;
	}

}

?>