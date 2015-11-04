<?php
namespace Typoheads\Formhandler\Component;
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * Component Manager originally written for the extension 'gimmefive'. 
 * This is a backport of the Component Manager of FLOW3. It's based
 * on code mainly written by Robert Lemke. Thanx to the FLOW3 team for all the great stuff!
 * 
 * Refactored for usage with Formhandler.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */

class Manager {
	const PACKAGE_PREFIX = 'Typoheads';
	const DIRECTORY_CLASSES = 'Classes/';
	const DIRECTORY_TEMPLATE = 'Resources/Template/';

	private static $instance = NULL;
	protected $packagePath;

	/**
	 * The global Formhandler values
	 *
	 * @access protected
	 * @var \Typoheads\Formhandler\Utility\Globals
	 */
	protected $globals;
	protected $additionalIncludePaths = NULL;

	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	protected function __construct() {
		$this->globals = \Typoheads\Formhandler\Utility\Globals::getInstance();
		$this->utilityFuncs = \Typoheads\Formhandler\Utility\GeneralUtility::getInstance();

		$this->loadTypoScriptConfig();
	}

	private function __clone() {}

	/**
	 * Loads the TypoScript config/setup for the formhandler on the current page.
	*/
	private function loadTypoScriptConfig() {
		if ($this->additionalIncludePaths === NULL) {
			$conf = array();
			$overrideSettings = $this->globals->getOverrideSettings();
			if (!is_array($overrideSettings['settings.'])) {
				$utilityFuncs = \Typoheads\Formhandler\Utility\GeneralUtility::getInstance();
				$setup = $GLOBALS['TSFE']->tmpl->setup;
				if (is_array($setup['plugin.']['Tx_Formhandler.']['settings.']['additionalIncludePaths.'])) {
					$conf = $setup['plugin.']['Tx_Formhandler.']['settings.']['additionalIncludePaths.'];
					$conf = $this->getParsedIncludePaths($conf);
				}
				if ($this->globals->getPredef() && is_array($setup['plugin.']['Tx_Formhandler.']['settings.']['predef.'][$this->globals->getPredef()]['additionalIncludePaths.'])) {
					$predefIncludePaths = $setup['plugin.']['Tx_Formhandler.']['settings.']['predef.'][$this->globals->getPredef()]['additionalIncludePaths.'];
					$predefIncludePaths = $this->getParsedIncludePaths($predefIncludePaths);
					$conf = array_merge($conf, $predefIncludePaths);
				}
			} elseif (is_array($overrideSettings['settings.']['additionalIncludePaths.'])) {
				$overrideSettings['settings.']['additionalIncludePaths.'] = $this->getParsedIncludePaths($overrideSettings['settings.']['additionalIncludePaths.']);
				$conf = $overrideSettings['settings.']['additionalIncludePaths.'];
			}
			if(TYPO3_MODE === 'BE') {
				$tsconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig(intval($_GET['id']), 'tx_formhandler_mod1'); 
				if (is_array($tsconfig['properties']['config.']['additionalIncludePaths.'])) {
					$conf = $tsconfig['properties']['config.']['additionalIncludePaths.'];
					$conf = $this->getParsedIncludePaths($conf);
				}
			}
			$this->additionalIncludePaths = $conf;
		}
	}

	protected function getParsedIncludePaths(array $pathsArray) {
		foreach($pathsArray as $key => &$path) {
			if(FALSE === strpos($key, '.')) {
				$path = $this->utilityFuncs->getSingle($pathsArray, $key);
				unset($pathsArray[$key . '.']);
			}
		}
		return $pathsArray;
	}

	/**
	 * Returns a component object from the cache. If there is no object stored already, a new one is created and stored in the cache.
	 *
	 * @param string $componentName 
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 */
	public function getComponent($componentName) {

		//Avoid component manager creating multiple instances of itself:
		if (get_class($this) === $componentName) {
			return $this;
		} elseif ('Typoheads\Formhandler\Utility\Globals' === $componentName) {
			return \Typoheads\Formhandler\Utility\Globals::getInstance();
		} elseif ('Typoheads\Formhandler\Utility\GeneralUtility' === $componentName) {
			return \Typoheads\Formhandler\Utility\GeneralUtility::getInstance();
		}
		
		$arguments =  array_slice(func_get_args(), 1, NULL, TRUE); 
		$componentObject = $this->createComponentObject($componentName, $arguments);

		return $componentObject;
	}

	/**
	 * Requires a class file and instantiates a class.
	 *
	 * @param string $componentName 
	 * @param array	$overridingConstructorArguments
	 * @return object
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 */
	protected function createComponentObject($componentName, array $overridingConstructorArguments) {	
		$className = $componentName;

		if (!class_exists($className, TRUE)) {
			throw new \Exception('No valid implementation class for component "' . $componentName . '" found while building the component object (Class "' . $className . '" does not exist).');
		}

		$constructorArguments = array();
		foreach ($overridingConstructorArguments as $index => $value) {
			$constructorArguments[$index] = $value;
		}
		$class = new \ReflectionClass($className);
		$constructorArguments = $this->autoWireConstructorArguments($constructorArguments, $class);
		$constructorArguments = $this->prepareConstructorArguments($constructorArguments);

		$componentObject = $class->newInstanceArgs($constructorArguments);

		if (!is_object($componentObject)) {
			$errorMessage = error_get_last();
			throw new \Exception('A parse error ocurred while trying to build a new object of type ' . $className . ' (' . $errorMessage['message'] . '). The evaluated PHP code was: ' . $instruction);
		}

		return $componentObject;
	}

	/**
	 * If mandatory constructor arguments have not been defined yet, this function tries to autowire
	 * them if possible.
	 *
	 * @param array $constructorArguments: Array of Tx_FLOW3_Component_ConfigurationArgument for the current component
	 * @param ReflectionClass $class: The component class which contains the methods supposed to be analyzed
	 * @return array The modified array of constructor arguments
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 */
	protected function autoWireConstructorArguments(array $constructorArguments, \ReflectionClass $class) {
		$className = $class->getName();
		$constructor = $class->getConstructor();
		if ($constructor !== NULL) {
			foreach ($constructor->getParameters() as $parameterIndex => $parameter) {
				$index = $parameterIndex + 1;
				if (!isset($constructorArguments[$index])) {
					try {
						if ($parameter->isOptional()) {
							$defaultValue = ($parameter->isDefaultValueAvailable()) ? $parameter->getDefaultValue() : NULL;
							$constructorArguments[$index] = $defaultValue;
						} elseif ($parameter->getClass() !== NULL) {
							$constructorArguments[$index] = $parameter->getClass()->getName();
						} elseif ($parameter->allowsNull()) {
							$constructorArguments[$index] = NULL;
						} else {
							$this->debugMessages[] = 'Tried everything to autowire parameter $' . $parameter->getName() . ' in ' . $className . '::' . $constructor->getName() . '() but I saw no way.';
						}
					} catch (ReflectionException $exception) {
						throw new \Exception('While trying to autowire the parameter $' . $parameter->getName() . ' of the method ' . $className . '::' . $constructor->getName() .'() a ReflectionException was thrown. Please verify the definition of your constructor method in ' . $constructor->getFileName() . ' line ' . $constructor->getStartLine() .'. Original message: ' . $exception->getMessage());
					}
				} else {
					$this->debugMessages[] = 'Did not try to autowire parameter $' . $parameter->getName() . ' in ' . $className . '::' . $constructor->getName() . '() because it was already set.';
				}
			}
		} else {
			$this->debugMessages[] = 'Autowiring for class ' . $className . ' disabled because no constructor was found.';
		}
		return $constructorArguments;
	}

	/**
	 * Checks and resolves dependencies of the constructor arguments and prepares an array of constructor
	 * arguments (strings) which can be used in a "new" statement to instantiate the component.
	 *
	 * @param array $constructorArguments: Array of constructor arguments for the current component
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 */
	public function prepareConstructorArguments($constructorArguments) {
		foreach ($constructorArguments as $index => $constructorArgument) {

			// TODO Testing the prefix is not very sophisticated. Should be is_object()
			if (substr($constructorArgument, 0, 10) === self::PACKAGE_PREFIX . '\\') {
				$value = $this->getComponent($constructorArgument);
			} else {
				$value = $constructorArgument;
			}
			$constructorArguments[$index] = $value;
		}
		return $constructorArguments;
	}

}