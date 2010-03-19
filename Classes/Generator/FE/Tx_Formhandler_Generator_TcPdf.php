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
		$this->pdf->SetFont('Helvetica', '', 12);
		$view = $this->componentManager->getComponent('Tx_Formhandler_View_PDF');
		
		$this->filename = FALSE;
		if(intval($this->settings['storeInTempFile']) === 1) {
			$this->outputPath = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT');
			if($this->settings['customTempOutputPath']) {
				$this->outputPath .= Tx_Formhandler_StaticFuncs::sanitizePath($this->settings['customTempOutputPath']);
			} else {
				$this->outputPath .= '/typo3temp/';
			}
			$this->filename = $this->outputPath . $this->settings['filePrefix'] . Tx_Formhandler_StaticFuncs::generateHash() . '.pdf';
				
			$this->filenameOnly = basename($this->filename);
			if($this->settings['staticFileName'] && $this->settings['staticFileName.']) {
				$this->filenameOnly = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'staticFileName');
			} elseif($this->settings['staticFileName']) {
				$this->filenameOnly = $this->settings['staticFileName'];
			}
		}
		
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
		
		$this->pdf->writeHTML($content);
		$returns = $this->settings['returnFileName'];

		if($this->filename !== FALSE) {
			$this->pdf->Output($this->filename, 'F');
			
			$downloadpath = $this->filename;
			
			if($returns) {
				
				return $downloadpath;
			}
			$downloadpath = str_replace(t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT'), '', $downloadpath);
			header('Location: ' . $downloadpath);
		} else {
			$this->pdf->Output('formhandler.pdf','I');
			
		}
	}
	
	public function getLink($linkGP) {
		$linkGP = array();
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		
		$target = '_blank';
        if($this->settings['target']) {
            $target = $this->settings['target'];
        }
        
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
		

		return $this->cObj->getTypolink('PDF', $GLOBALS['TSFE']->id, $linkGP, $target);
	}
}

?>