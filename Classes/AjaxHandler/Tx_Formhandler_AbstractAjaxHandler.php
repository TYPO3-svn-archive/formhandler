<?php

abstract class Tx_Formhandler_AbstractAjaxHandler extends Tx_Formhandler_AbstractClass {

	abstract public function initAjax();

	public function init($settings) {
		$this->settings = $settings;
	}

	abstract public function fillAjaxMarkers(&$markers);

}

?>