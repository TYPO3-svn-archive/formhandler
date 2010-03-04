<?php

class Tx_Formhandler_Generator_PdfGenerator extends Tx_Formhandler_AbstractGenerator {
	
	/**
	 * Renders the PDF.
	 *
	 * @return void
	 */
	public function process() {
		
	}
	
	
	
	public function getLink($linkGP) {
		$params = array();

		$url = Tx_Formhandler_StaticFuncs::getHostname() . $this->cObj->getTypolink_URL($GLOBALS['TSFE']->id, $linkGP);

        $target = '_blank';
        if($this->settings['target']) {
            $target = $this->settings['target'];
        }

        $type = 123;
        if($this->settings['type']) {
            $type = $this->settings['type'];
        }

		$params = array(
			'type' => $type,
			'no_cache' => 1,
			'submitted_ok' => 1
		);

        $params = array_merge($params, $linkGP);

		return $this->cObj->getTypolink('PDF', $GLOBALS['TSFE']->id, $params, $target);
	}
}

?>