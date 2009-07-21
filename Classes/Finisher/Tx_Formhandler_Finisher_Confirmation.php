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
 * A finisher showing the content of ###TEMPLATE_CONFIRMATION### replacing all common Formhandler markers
 * plus ###PRINT_LINK###, ###PDF_LINK### and ###CSV_LINK###.
 *
 * The finisher sets a flag in $_SESSION, so that Formhandler will only call this finisher and nothing else if the user reloads the page.
 *
 * A sample configuration looks like this:
 * <code>
 * finishers.3.class = Tx_Formhandler_Finisher_Confirmation
 * finishers.3.config.returns = 1
 * finishers.3.config.pdf.class = Tx_Formhandler_Generator_PDF
 * finishers.3.config.pdf.exportFields = firstname,lastname,interests,pid,ip,submission_date
 * finishers.3.config.pdf.export2File = 1
 * finishers.3.config.csv.class = Tx_Formhandler_Generator_CSV
 * finishers.3.config.csv.exportFields = firstname,lastname,interests
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_Confirmation extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		//set session value to prevent another validation or finisher circle. Formhandler will call only this Finisher if the user reloads the page.
		session_start();
		$_SESSION['submitted_ok'] = 1;

		//read template file
		if(!$this->templateFile) {
			if (!isset($this->settings['templateFile'])) {
				Tx_Formhandler_StaticFuncs::debugArray($this->settings);
				Tx_Formhandler_StaticFuncs::throwException('no_config_confirmation', 'Tx_Formhandler_Finisher_Confirmation', 'templateFile');
			}
			$templateFile = $this->settings['templateFile'];
			if(isset($this->settings['templateFile.']) && is_array($this->settings['templateFile.'])) {
				$this->templateFile = $this->cObj->cObjGetSingle($this->settings['templateFile'], $this->settings['templateFile.']);
			} else {
				$this->templateFile = t3lib_div::getURL(Tx_Formhandler_StaticFuncs::resolvePath($templateFile));
			}
		}

		//set view
		$view = $this->componentManager->getComponent('Tx_Formhandler_View_Confirmation');
			
		//render pdf
		if(!strcasecmp($this->gp['renderMethod'], 'pdf')) {
				
			//set language file
			if(isset($this->settings['langFile.']) && is_array($this->settings['langFile.'])) {
				$langFile = $this->cObj->cObjGetSingle($this->settings['langFile'], $this->settings['langFile.']);
			} else {
				$langFile = Tx_Formhandler_StaticFuncs::resolveRelPathFromSiteRoot($this->settings['langFile']);
			}
			$generatorClass = $this->settings['pdf.']['class'];
			if(!$generatorClass) {
				$generatorClass = 'Tx_Formhandler_Generator_PDF';
			}
			$generatorClass = Tx_Formhandler_StaticFuncs::prepareClassName($generatorClass);
			$generator = $this->componentManager->getComponent($generatorClass);
			$exportFields = array();
			if($this->settings['pdf.']['exportFields']) {
				$exportFields = t3lib_div::trimExplode(',', $this->settings['pdf.']['exportFields']);
			}
			$file = "";
			if($this->settings['pdf.']['export2File']) {
				//tempnam seems to be buggy and insecure
				//$file = tempnam("typo3temp/","/formhandler_").".pdf";

				//using random numbered file for now
				$file = 'typo3temp/formhandler__' . rand(0,getrandmax()) . '.pdf';
			}
			$generator->setTemplateCode($this->templateFile);
			$generator->generateFrontendPDF($this->gp, $langFile, $exportFields, $file);
				
			//render csv
		} elseif(!strcasecmp($this->gp['renderMethod'],"csv")) {
			$generatorClass = $this->settings['csv.']['class'];
			if(!$generatorClass) {
				$generatorClass = 'Tx_Formhandler_Generator_CSV';
			}
			$generatorClass = Tx_Formhandler_StaticFuncs::prepareClassName($generatorClass);
			$generator = $this->componentManager->getComponent($generatorClass);
			$exportFields = array();
			if($this->settings['csv.']['exportFields']) {
				$exportFields = t3lib_div::trimExplode(',', $this->settings['csv.']['exportFields']);
			}
			$generator->generateFrontendCSV($this->gp, $exportFields);
		}

		//show TEMPLATE_CONFIRMATION
		$view->setTemplate($this->templateFile, ('CONFIRMATION' . $this->settings['templateSuffix']));
		if(!$view->hasTemplate()) {
			$view->setTemplate($this->templateFile, 'CONFIRMATION');
			if(!$view->hasTemplate()) {
				Tx_Formhandler_StaticFuncs::debugMessage('no_confirmation_template');
			}
		}
		
		$view->setSettings($this->settings);
		return $view->render($this->gp,array());
	}

}
?>
