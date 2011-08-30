<?php
abstract class Tx_Formhandler_AbstractGenerator extends Tx_Formhandler_AbstractComponent {

	public function getLink($linkGP) {
		$text = $this->getLinkText();

		$params = $this->getDefaultLinkParams();
		$componentParams = $this->getComponentLinkParams($linkGP);
		if (is_array($componentParams)) {
			$params = t3lib_div::array_merge_recursive_overrule($params, $componentParams);
		}
		return $this->cObj->getTypolink($text, $GLOBALS['TSFE']->id, $params, $this->getLinkTarget());
	}

	protected function getDefaultLinkParams() {
		$prefix = Tx_Formhandler_Globals::$formValuesPrefix;
		$tempParams = array(
			'tstamp' => Tx_Formhandler_Globals::$session->get('inserted_tstamp'),
			'hash' => Tx_Formhandler_Globals::$session->get('key_hash')
		);
		$params = array();
		if ($prefix) {
			$params[$prefix] = $tempParams;
		} else {
			$params = $tempParams;
		}
		return $params;
	}
	
	abstract protected function getComponentLinkParams($linkGP);
	
	protected function getLinkText() {
		$text = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'linkText');
		if(strlen($text) === 0) {
			$text = 'Save';
		}
		return $text;
	}

	protected function getLinkTarget() {
		$target = Tx_Formhandler_StaticFuncs::getSingle($this->settings, 'linkTarget');
		if(strlen($target) === 0) {
			$target = '_self';
		}
		return $target;
	}
}
?>