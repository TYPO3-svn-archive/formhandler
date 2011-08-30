<?php

require_once(PATH_tslib . 'interfaces/interface.tslib_content_stdwraphook.php'); 
class tx_formhandler_stdwrap implements tslib_content_stdWrapHook {

	private $originalGET;
	private $originalPOST;
	
	/**
	 * Hook for modifying $content before core's stdWrap does anything
	 *
	 * @param	string		input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
	 * @param	array		TypoScript stdWrap properties
	 * @param	tslib_cObj	parent content object
	 * @return	string		further processed $content
	 */
	public function stdWrapPreProcess($content, array $configuration, tslib_cObj &$parentObject) {
		if(intval($configuration['sanitize']) === 1) {
			$this->originalGET = $_GET;
			$this->originalPOST = $_POST;
			$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
			$_GET[$prefix] = Tx_Formhandler_Globals::$gp;
			$_POST[$prefix] = Tx_Formhandler_Globals::$gp;
		}
		return $content;
	}

	/**
	 * Hook for modifying $content after core's stdWrap has processed setContentToCurrent, setCurrent, lang, data, field, current, cObject, numRows, filelist and/or preUserFunc
	 *
	 * @param	string		input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
	 * @param	array		TypoScript stdWrap properties
	 * @param	tslib_cObj	parent content object
	 * @return	string		further processed $content
	 */
	public function stdWrapOverride($content, array $configuration, tslib_cObj &$parentObject) {
		return $content;
	}

	/**
	 * Hook for modifying $content after core's stdWrap has processed override, preIfEmptyListNum, ifEmpty, ifBlank, listNum, trim and/or more (nested) stdWraps
	 *
	 * @param	string		input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
	 * @param	array		TypoScript "stdWrap properties".
	 * @param	tslib_cObj	parent content object
	 * @return	string		further processed $content
	 */
	public function stdWrapProcess($content, array $configuration, tslib_cObj &$parentObject) {
		return $content;
	}

	/**
	 * Hook for modifying $content after core's stdWrap has processed anything but debug
	 *
	 * @param	string		input value undergoing processing in this function. Possibly substituted by other values fetched from another source.
	 * @param	array		TypoScript stdWrap properties
	 * @param	tslib_cObj	parent content object
	 * @return	string		further processed $content
	 */
	public function stdWrapPostProcess($content, array $configuration, tslib_cObj &$parentObject) {
		if(intval($configuration['sanitize']) === 1) {
			$_GET = $this->originalGET;
			$_POST = $this->originalPOST;
		}
		return $content;
	}
}

?>
