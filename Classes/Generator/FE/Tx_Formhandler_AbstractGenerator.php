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
		$prefix = $this->globals->getFormValuesPrefix();
		$tempParams = array(
			'tstamp' => $this->globals->getSession()->get('inserted_tstamp'),
			'hash' => $this->globals->getSession()->get('unique_hash')
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
		$text = $this->utilityFuncs->getSingle($this->settings, 'linkText');
		if(strlen($text) === 0) {
			$text = 'Save';
		}
		return $text;
	}

	protected function getLinkTarget() {
		$target = $this->utilityFuncs->getSingle($this->settings, 'linkTarget');
		if(strlen($target) === 0) {
			$target = '_self';
		}
		return $target;
	}
}
?>