<?php

abstract class Tx_Formhandler_AbstractComponent extends Tx_Formhandler_AbstractClass {

	/**
	 * The GET/POST parameters
	 *
	 * @access protected
	 * @var array
	 */
	protected $gp;

	/**
	 * Settings
	 * 
	 * @access protected
	 * @var array
	 */
	protected $settings;



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