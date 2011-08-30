<?php

abstract class Tx_Formhandler_AbstractAjaxHandler {

	/**
	 * The component manager
	 *
	 * @access protected
	 * @var Tx_Formhandler_Component_Manager
	 */
	protected $componentManager;

	/**
	 * The global Formhandler configuration
	 *
	 * @access protected
	 * @var Tx_Formhandler_Configuration
	 */
	protected $configuration;

	/**
	 * The cObj
	 *
	 * @access protected
	 * @var tslib_cObj
	 */
	protected $cObj;

	/**
	 * The constructor for a finisher setting the component manager, configuration and the repository.
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @param Tx_DataProvider_Repository $repository
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->cObj = Tx_Formhandler_Globals::$cObj;

	}

	abstract public function initAjax();

	public function init($settings) {
		$this->settings = $settings;
	}

	abstract public function fillAjaxMarkers(&$markers);

}

?>