<?php

class Tx_Formhandler_Generator_Print extends Tx_Formhandler_AbstractGenerator {

	/**
	 * Unused
	 */
	public function process() {

	}

	protected function getLinkText() {
		$text = $this->utilityFuncs->getSingle($this->settings, 'linkText');
		if (strlen($text) == 0) {
			$text = $this->utilityFuncs->getTranslatedMessage($this->globals->getLangFiles(), 'print');
			
		}
		if (strlen($text) === 0) {
			$text = 'Print';
		}
		return $text;
	}

	protected function getComponentLinkParams($linkGP) {
		$prefix = $this->globals->getFormValuesPrefix();
		$tempParams = array(
			'action' => 'show'
		);
		$params = array();
		if ($prefix) {
			$params[$prefix] = $tempParams;
		} else {
			$params = $tempParams;
		}
		$params['type'] = 98;
		return $params;
	}
}