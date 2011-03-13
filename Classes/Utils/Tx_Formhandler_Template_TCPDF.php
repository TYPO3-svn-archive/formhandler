<?php

/* $Id$ */

if (TYPO3_MODE == 'BE') {
	require_once('../../../Resources/PHP/tcpdf/tcpdf.php');
} else {
	require_once('typo3conf/ext/formhandler/Resources/PHP/tcpdf/tcpdf.php');
}

/**
 * A PDF Template class for Formhandler generated PDF files for usage with Generator_TCPDF.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Utils
 */
class Tx_Formhandler_Template_TCPDF extends TCPDF {

	/**
	 * Path to language file
	 *
	 * @access protected
	 * @var string
	 */
	protected $sysLangFile;

	/**
	 * Text for the header
	 *
	 * @access protected
	 * @var string
	 */
	protected $headerText;

	/**
	 * Text for the footer
	 *
	 * @access protected
	 * @var string
	 */
	protected $footerText;

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
		$headerText = $this->getHeaderText();
		if(strlen($headerText) > 0) {
			$this->SetY(5);
			$this->SetFont('Helvetica', 'I', 8);
			$text = str_ireplace(
					array(
						'###PDF_PAGE_NUMBER###',
						'###PDF_TOTAL_PAGES###'
					),
					array(
						$this->PageNo(),
						$this->numpages
					),
					$headerText
			);
			$this->Cell(0, 10, $text, 'B', 0, 'C');
		}
	}

	/**
	 * Generates the footer
	 * 
	 * @return void
	 */
	public function Footer() {

		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		$this->SetFont('Helvetica', 'I', 8);

		$footerText = $this->getFooterText();

		if(strlen($footerText) > 0) {
			$footerText = str_ireplace(
					array(
						'###PDF_PAGE_NUMBER###',
						'###PDF_TOTAL_PAGES###'
					),
					array(
						$this->PageNo(),
						$this->numpages
					),
					$footerText
			);
			$this->Cell(0, 10, $footerText, 'T', 0, 'C');
		} else {
			$text = $this->getLL('footer_text');
			$text = sprintf($text,date('d.m.Y H:i:s', time()));
			$this->Cell(0, 10, $text, 'T', 0, 'C');
			$pageNumbers = $this->getLL('page') . ' ' . $this->PageNo() . '/' . $this->numpages;
			$this->Cell(0, 10, $pageNumbers, 'T', 0, 'R');
		}
		
		
	}

	private function getLL($key) {
		global $LANG;
		if (TYPO3_MODE == 'BE') {
			$LANG->includeLLFile($this->sysLangFile);
			$text = trim($LANG->getLL($key));
		} else {
			$text = trim($GLOBALS['TSFE']->sL('LLL:' . $this->sysLangFile . ':' . $key));
		}
		return $text;
	}

	/**
	 * Set the text for the PDF Header
	 *
	 * @param string $s The string to set as PDF Header Text
	 */
	public function setHeaderText($s) {
		$this->headerText = $s;
	}

	/**
	 * Set the text for the PDF Footer
	 *
	 * @param string $s The string to set as PDF Header Text
	 */
	public function setFooterText($s) {
		$this->footerText = $s;
	}

	/**
	 * Returns the string used as PDF Footer text
	 *
	 * @return string
	 */
	public function getHeaderText() {
		return $this->headerText;
	}

	/**
	 * Returns the string used as PDF Footer text
	 *
	 * @return string
	 */
	public function getFooterText() {
		return $this->footerText;
	}

}
?>