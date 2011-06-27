<?php

abstract class Tx_Formhandler_AbstractClass {

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
	 * The global Formhandler values
	 *
	 * @access protected
	 * @var Tx_Formhandler_Globals
	 */
	protected $globals;

	/**
	 * The Formhandler utility methods
	 *
	 * @access protected
	 * @var Tx_Formhandler_UtilityFuncs
	 */
	protected $utlityFuncs;

	/**
	 * The cObj
	 *
	 * @access protected
	 * @var tslib_cObj
	 */
	protected $cObj;

	/**
	 * The constructor for an interceptor setting the component manager and the configuration.
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @return void
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager, 
								Tx_Formhandler_Configuration $configuration, 
								Tx_Formhandler_Globals $globals,
								Tx_Formhandler_UtilityFuncs $utilityFuncs) {

		$this->componentManager = $componentManager;
		$this->configuration = $configuration;
		$this->globals = $globals;
		$this->utilityFuncs = $utilityFuncs;
		$this->cObj = $this->globals->getCObj();
	}
}
?>