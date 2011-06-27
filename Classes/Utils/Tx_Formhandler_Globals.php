<?php

class Tx_Formhandler_Globals {

	static private $instance = NULL;

	protected $ajaxHandler;
	protected $cObj;
	protected $debuggers;
	protected $formID;
	protected $formValuesPrefix;
	protected $gp;
	protected $langFiles;
	protected $overrideSettings;
	protected $predef;
	protected $randomID;
	protected $session;
	protected $settings;
	protected $templateCode;
	protected $templateSuffix;

	static public function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Tx_Formhandler_Globals();
		}
		return self::$instance;
	}

	protected function __construct() {}
	protected function __clone() {}

	public function setAjaxHandler($ajaxHandler) {
		$this->ajaxHandler = $ajaxHandler;
	}

	public function setCObj($cObj) {
		$this->cObj = $cObj;
	}

	public function setDebuggers($debuggers) {
		$this->debuggers = $debuggers;
	}

	public function addDebugger($debugger) {
		if(!is_array($this->debuggers)) {
			$this->debuggers = array();
		}
		$this->debuggers[] = $debugger;
	}
	
	public function setFormID($formID) {
		$this->formID = $formID;
	}
	
	public function setFormValuesPrefix($formValuesPrefix) {
		$this->formValuesPrefix = $formValuesPrefix;
	}

	public function setGP($gp) {
		$this->gp = $gp;
	}

	public function setLangFiles($langFiles) {
		$this->langFiles = $langFiles;
	}

	public function setOverrideSettings($overrideSettings) {
		$this->overrideSettings = $overrideSettings;
	}

	public function setPredef($predef) {
		$this->predef = $predef;
	}

	public function setRandomID($randomID) {
		$this->randomID = $randomID;
	}

	public function setSession($session) {
		$this->session = $session;
	}

	public function setSettings($settings) {
		$this->settings = $settings;
	}

	public function setTemplateCode($templateCode) {
		$this->templateCode = $templateCode;
	}

	public function setTemplateSuffix($templateSuffix) {
		$this->templateSuffix = $templateSuffix;
	}

	public function getAjaxHandler() {
		return $this->ajaxHandler;
	}

	public function getCObj() {
		return $this->cObj;
	}

	public function getDebuggers() {
		return $this->debuggers;
	}

	public function getFormID() {
		return $this->formID;
	}

	public function getFormValuesPrefix() {
		return $this->formValuesPrefix;
	}

	public function getGP() {
		return $this->gp;
	}

	public function getLangFiles() {
		return $this->langFiles;
	}

	public function getOverrideSettings() {
		return $this->overrideSettings;
	}

	public function getPredef() {
		return $this->predef;
	}

	public function getRandomID() {
		return $this->randomID;
	}

	public function getSession() {
		return $this->session;
	}

	public function getSettings() {
		return $this->settings;
	}

	public function getTemplateCode() {
		return $this->templateCode;
	}

	public function getTemplateSuffix() {
		return $this->templateSuffix;
	}
}

?>