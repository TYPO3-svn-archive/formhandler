<?php

class Tx_Formhandler_Generator_TcPdf extends Tx_Formhandler_AbstractGenerator {
	
	/**
	 * Renders the CSV.
	 *
	 * @return void
	 */
	public function process() {

		$this->pdf = $this->componentManager->getComponent('Tx_Formhandler_Template_TCPDF');
		$this->pdf->AddPage();
		$this->pdf->SetFont('Freesans', '', 12);
		$view = $this->componentManager->getComponent('Tx_Formhandler_View_PDF');
		
		
		$this->formhandlerSettings = Tx_Formhandler_Globals::$settings;
		
		$suffix = $this->formhandlerSettings['templateSuffix'];
		$this->templateCode = Tx_Formhandler_StaticFuncs::readTemplateFile(FALSE, $this->formhandlerSettings);
		if($suffix) {
			$view->setTemplate($this->templateCode, 'PDF' . $suffix);
		}
		if(!$view->hasTemplate()) {
			$view->setTemplate($this->templateCode, 'PDF');
		}
		if(!$view->hasTemplate()) {
			Tx_Formhandler_StaticFuncs::throwException('no_pdf_template');
		}
		
		$view->setComponentSettings($this->settings);
		$content = $view->render($this->gp, array());
		
		$pdf = $this->componentManager->getComponent('Tx_Formhandler_Template_TCPDF');
		
		$pdf->writeHTML(stripslashes($content), true, 0);

		if(strlen($file) > 0) {
			$pdf->Output($file, 'F');
			$pdf->Close();
			$downloadpath = $file;
			if($returns) {
				return $downloadpath;
			}
			
			header('Location: ' . $downloadpath);
		} else {
			$pdf->Output('formhandler.pdf','D');
		}
	}
	
	public function getLink($linkGP) {
		$linkGP = array();
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		if($prefix) {
			$linkGP[$prefix]['action'] = 'pdf';
			$linkGP[$prefix]['submitted'] = '1';
			$linkGP[$prefix]['submitted_ok'] = '1';
		} else {
			$linkGP['action'] = 'pdf';
			$linkGP['submitted'] = '1';
			$linkGP['submitted_ok'] = '1';
		}
		$linkGP['no_cache'] = 1;
		$linkGP['dontReset'] = 1;
		return $this->cObj->getTypolink('PDF', $GLOBALS['TSFE']->id, $linkGP);
	}
}

?>