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
 * $Id: Tx_Formhandler_Generator_PDF.php 22614 2009-07-21 20:43:47Z fabien_u $
 *                                                                        */

/**
 * Class to generate PDF files in Backend and Frontend
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Generator
 * @uses Tx_Formhandler_Template_PDF
 */
class Tx_Formhandler_Generator_WebkitPDF {

	/**
	 * The GimmeFive component manager
	 *
	 * @access protected
	 * @var Tx_GimmeFive_Component_Manager
	 */
	protected $componentManager;

	/**
	 * Default Constructor
	 *
	 * @param Tx_GimmeFive_Component_Manager $componentManager The component manager of GimmeFive
	 * @return void
	 */
	public function __construct(Tx_GimmeFive_Component_Manager $componentManager) {
		$this->componentManager = $componentManager;

	}

	/**
	 * Function to generate a PDF file from submitted form values. This function is called by Tx_Formhandler_Controller_Backend
	 *
	 * @param array $records The records to export to PDF
	 * @param array $exportFields A list of fields to export. If not set all fields are exported
	 * @see Tx_Formhandler_Controller_Backend::generatePDF()
	 * @return void
	 */
	function generateModulePDF($records, $exportFields = array()) {


	}

	/**
	 * Function to generate a PDF file from submitted form values. This function is called by Tx_Formhandler_Finisher_Confirmation and Tx_Formhandler_Finisher_Mail
	 *
	 * @param array $gp The values to export
	 * @param string $langFile The translation file configured in TypoScript of Formhandler
	 * @param array $exportFields A list of fields to export. If not set all fields are exported
	 * @param string $file A filename to save the PDF in. If not set, the PDF will be rendered directly to screen
	 * @param boolean $returns If set, the PDF will be rendered into the given file, if not set, the PDF will be rendered into the file and afterwards directly to screen
	 * @see Tx_Formhandler_Finisher_Confirmation::process()
	 * @see Tx_Formhandler_Finisher_Mail::parseMailSettings()
	 * @return void|filename
	 */
	function generateFrontendPDF($gp, $langFile, $exportFields = array(), $file = '', $returns = false) {
		session_start();
		
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		$generatorPage = $GLOBALS['TSFE']->id;
		$params = array(
			'no_cache' => 1,
			'tx_webkitpdf_pi1' => array(
				'urls' => array(
					$url
				)
			)
		);
		
		
		Tx_Formhandler_StaticFuncs::doRedirect($generatorPage, FALSE, $params);
	}
	
	public function setTemplateCode($templateCode) {
		return;
	}

}
?>
