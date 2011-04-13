<?php

abstract class Tx_Formhandler_AbstractComponent {

	/**
	 * The Formhandler component manager
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
	 * The GET/POST parameters
	 *
	 * @access protected
	 * @var array
	 */
	protected $gp;

	/**
	 * The cObj
	 *
	 * @access protected
	 * @var tslib_cObj
	 */
	protected $cObj;

	/**
	 * Settings
	 * 
	 * @access protected
	 * @var array
	 */
	protected $settings;

	/**
	 * The constructor for an interceptor setting the component manager and the configuration.
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, Tx_Formhandler_Configuration $configuration) {
		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->cObj = Tx_Formhandler_Globals::$cObj;
	}

	/**	
	 * Initialize the class variables
	 *
	 * @param array $gp GET and POST variable array
	 * @param array $settings Typoscript configuration for the component (component.1.config.*)
	 *
	 * @return void
	 */
	public function init($gp, $settings) {
		$this->gp = $gp;
		$this->settings = $settings;
	}

	/**
	 * The main method called by the controller
	 *
	 * @param array $gp The GET/POST parameters
	 * @param array $settings The defined TypoScript settings for the finisher
	 * @return array The probably modified GET/POST parameters
	 */
	abstract public function process();

	public function validateConfig() {

	}

}

?>