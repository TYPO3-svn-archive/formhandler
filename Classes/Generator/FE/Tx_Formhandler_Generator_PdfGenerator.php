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
		$tempParams = array(
			'action' => 'show'
		);
		$params = array();
		if ($prefix) {
			$params[$prefix] = $tempParams;
		} else {
			$params = $tempParams;
		}
		$type = 123;
		if ($this->settings['type']) {
			$type = $this->settings['type'];
		}
		$params = array();
		$params['type'] = $type;
		return $params;
	}

}

?>