<?php

/* $Id$ */

if (TYPO3_MODE=="BE")   {
	require_once("../../../Resources/PHP/fpdf/fpdf.php");
} else {
	require_once("typo3conf/ext/formhandler/Resources/PHP/fpdf/fpdf.php");
}

/**
 * A PDF Template class for Formhandler generated PDF files
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Utils
 */
class Tx_Formhandler_Template_PDF extends FPDF {

	/**
	 * Path to language file
	 *
	 * @access protected
	 * @var string
	 */
	protected $sysLangFile;
	
	public function __construct() {
		parent::__construct();
		$this->sysLangFile = 'EXT:formhandler/Resources/Language/locallang.xml';
		
	}

	/**
	 * Generates the header of the page
	 * 
	 * @return void
	 */
	public function Header() {
	  
		//Arial bold 15
		$this->SetFont('Helvetica', 'B', 15);
	  
		
		//Title
		$this->Cell(0, 10, $this->getLL('submission_details'), 'B', 0, 'C');
	  
		//Line break
		$this->Ln(20);
	}

	/**
	 * Generates the footer of the page
	 * 
	 * @return void
	 */
	public function Footer() {
	  
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
	  
		//Arial italic 8
		$this->SetFont('Helvetica', 'I', 8);
	    
		$text = $this->getLL('footer_text');
		$text = sprintf($text,date('d.m.Y H:i:s', time()));
		$this->Cell(0,10,$text,'T',0,'C');
		$pageNumbers = $this->getLL('page') . ' ' . $this->PageNo() . '/{nb}';
		$this->Cell(0, 10, $pageNumbers, 'T', 0, 'R');
	}
	
	private function getLL($key) {
		global $LANG;
		if (TYPO3_MODE=="BE")   {
			$LANG->includeLLFile($this->sysLangFile);
			return trim($LANG->getLL($key));
		} else {
			return trim($GLOBALS['TSFE']->sL('LLL:' . $this->sysLangFile . ':' . $key));
		}
	}

}
?>
