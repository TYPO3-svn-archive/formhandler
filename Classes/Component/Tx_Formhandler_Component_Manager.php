<?php
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
 * @package	TYPO3
 * @subpackage	Tx_Formhandler
 */
require_once(t3lib_extMgm::extPath('formhandler') . 'Classes/Utils/Tx_Formhandler_Globals.php');
class Tx_Formhandler_Component_Manager {
	const PACKAGE_PREFIX = 'Tx';
	const DIRECTORY_CLASSES = 'Classes/';
	const DIRECTORY_TEMPLATE = 'Resources/Template/';

	private static $instance = NULL;

	protected $classFiles;
	protected $packagePath;

	protected $componentObjects = array(); // the object cache
	protected $additionalIncludePaths = NULL;

	protected $cacheFilePath = '';

	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Tx_Formhandler_Component_Manager();
		}
		return self::$instance;
	}

	protected function __construct() {
		$this->cacheFilePath = PATH_site . 'typo3temp/formhandlerClassesCache.txt';
		if(file_exists($this->cacheFilePath)) {
			$this->classFiles = unserialize(file_get_contents($this->cacheFilePath));
		} else {
			$this->classFiles = array();
		}
		$this->loadTypoScriptConfig();
		spl_autoload_register(array($this, 'loadClass'));
	}

	private function __clone() {}

	/**
	 * Loads the TypoScript config/setup for the formhandler on the current page.
	*/
	private function loadTypoScriptConfig() {
		if ($this->additionalIncludePaths === NULL) {
			$conf = array();
			if (!is_array(Tx_Formhandler_Globals::$overrideSettings['settings.'])) {
				$setup = $GLOBALS['TSFE']->tmpl->setup;
				if ($setup['plugin.']['Tx_Formhandler.']['settings.']['additionalIncludePaths.']) {
					$conf = $setup['plugin.']['Tx_Formhandler.']['settings.']['additionalIncludePaths.'];
				}
				if (Tx_Formhandler_Globals::$predef && is_array($setup['plugin.']['Tx_Formhandler.']['settings.']['predef.'][Tx_Formhandler_Globals::$predef]['additionalIncludePaths.'])) {
					$conf = array_merge($conf, $setup['plugin.']['Tx_Formhandler.']['settings.']['predef.'][Tx_Formhandler_Globals::$predef]['additionalIncludePaths.']);
				}
			} elseif (Tx_Formhandler_Globals::$overrideSettings['settings.']['additionalIncludePaths.']) {
				$conf = Tx_Formhandler_Globals::$overrideSettings['settings.']['additionalIncludePaths.'];
			}
			$this->additionalIncludePaths = $conf;
		}
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
		}

		if (!is_array($this->classFiles)) {
			$this->classFiles = array();
		}
		$classNameParts = explode('_', $componentName, 3);
		if (!is_array($this->classFiles[$classNameParts[1]])) {
			$this->classFiles[$classNameParts[1]] = array();
		}
		if (!array_key_exists($componentName, $this->classFiles[$classNameParts[1]])) {
			$this->loadClass($componentName);
			$componentObject = $this->createComponentObject($componentName, array());
		} else {
			$arguments =  array_slice(func_get_args(), 1, NULL, TRUE); // array keys are preserved (TRUE) -> argument array starts with key=1 
			$componentObject = $this->createComponentObject($componentName, $arguments);
		}
		return $componentObject;
	}

	/**
	 * Requires a class file and instanciates a class.
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
			throw new Exception('No valid implementation class for component "' . $componentName . '" found while building the component object (Class "' . $className . '" does not exist).');
		}

		$constructorArguments = array();
		foreach ($overridingConstructorArguments as $index => $value) {
			$constructorArguments[$index] = $value;
		}
		$class = new ReflectionClass($className);
		$constructorArguments = $this->autoWireConstructorArguments($constructorArguments, $class);
		$injectedArguments = array();
		$preparedArguments = array();
		$this->injectConstructorArguments($constructorArguments, $injectedArguments, $preparedArguments);

		$instruction = '$componentObject = new ' . $className .'(';
		$instruction .= implode(', ',$preparedArguments);
		$instruction .= ');';

		eval($instruction);

		if (!is_object($componentObject)) {
			$errorMessage = error_get_last();
			throw new Exception('A parse error ocurred while trying to build a new object of type ' . $className . ' (' . $errorMessage['message'] . '). The evaluated PHP code was: ' . $instruction);
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
	protected function autoWireConstructorArguments(array $constructorArguments, ReflectionClass $class) {
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
						throw new Exception('While trying to autowire the parameter $' . $parameter->getName() . ' of the method ' . $className . '::' . $constructor->getName() .'() a ReflectionException was thrown. Please verify the definition of your constructor method in ' . $constructor->getFileName() . ' line ' . $constructor->getStartLine() .'. Original message: ' . $exception->getMessage());
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
	 * @param array &$injectedArguments: An empty array passed by reference. Will contain instances of the components which were injected into the constructor
	 * @param array &$preparedArguments: An empty array passed by reference: Will contain constructor parameters as strings to be used in a new statement
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 */
	public function injectConstructorArguments($constructorArguments, &$injectedArguments, &$preparedArguments) {
		foreach ($constructorArguments as $index => $constructorArgument) {
			// TODO Testing the prefix is not very sophisticated. Should be is_object()
			if (substr($constructorArgument, 0, 3) === self::PACKAGE_PREFIX . '_') {
				$value = $this->getComponent($constructorArgument);
			} else {
				$value = $constructorArgument;
			}
			if (is_string($value)) {
				$escapedValue = str_replace("'", "\\'", str_replace('\\', '\\\\', $value));
				$preparedArguments[] = "'" . $escapedValue . "'";
			} elseif (is_numeric($value)) {
				$preparedArguments[] = $value;
			} elseif ($value === NULL) {
				$preparedArguments[] = 'NULL';
			} else {
				$preparedArguments[] = '$injectedArguments[' . $index . ']';
				$injectedArguments[$index] = $value;
			}
		}
	}

	/**
	 * Tests if a component object already exists in cache 
	 *
	 * @param string $componentName 
	 * @return void
	 */
	protected function componentObjectExists($componentName) {
		if (!is_string($componentName)) {
			throw new InvalidArgumentException('The component name must be of type string, ' . gettype($componentName) . ' given.');
		}
		return array_key_exists($componentName, $this->componentObjects);
	}

	/**
	 * Builds and returns an array of class names => file names of all
	 * tx_*.php files in the extension's Classes directory and its sub-
	 * directories.
	 *
	 * @param string $packageKey
	 * @param string $subDirectory: for recursion 
	 * @param int $recursionLevel: maximum depth of recursion
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 * @author adapted for TYPO3v4 by Jochen Rau <jochen.rau@typoplanet.de>
	 * @throws Exception if recursion into directories was too deep or another error occurred
	 */
	protected function buildArrayOfClassFiles($packageKey, $subDirectory = '', $recursionLevel = 0) {
		$classFiles = array();
		if (strpos($packageKey, '/') === FALSE) {
			$currentPath = $this->getPackagePath($packageKey) . self::DIRECTORY_CLASSES . $subDirectory;
		} else {
			$currentPath = $this->getPackagePath($packageKey) . $subDirectory;
		}

		// special handling for extension keys with underscores
		if (!is_dir($currentPath)) {
			$packageKey{0} = strtolower($packageKey{0});
			$packageKey = preg_replace('/([A-Z])/', '_$1', $packageKey);
			$packageKey = strtolower($packageKey);
			$currentPath = $this->getPackagePath($packageKey) . $subDirectory;
		}
		if (!is_dir($currentPath)) {
			return array();
		}
		if ($recursionLevel > 100) {
			throw new Exception('Recursion too deep.');
		}

		try {
			$classesDirectoryIterator = new DirectoryIterator($currentPath);
			while ($classesDirectoryIterator->valid()) {
				$filename = $classesDirectoryIterator->getFilename();
				if ($filename{0} !== '.') {
					if (is_dir($currentPath . $filename)) {
						$classFiles = array_merge($classFiles, $this->buildArrayOfClassFiles($packageKey, $subDirectory . $filename . '/', ($recursionLevel+1)));
					} else {
						if (substr($filename, 0, 3) === self::PACKAGE_PREFIX . '_' && substr($filename, -4, 4) === '.php') {
							$classFiles[substr($filename, 0, -4)] = $currentPath . $filename;
						}
					}
				}
				$classesDirectoryIterator->next();
			}
		} catch(Exception $exception) {
			throw new Exception($exception->getMessage());
		}
		return $classFiles;
	}

	/**
	 * Loads php files containing classes or interfaces found in the classes directory of
	 * a package and specifically registered classes.
	 *
	 * @param   string $className: Name of the class/interface to load
	 * @return  void
	 * @author  Jochen Rau <jochen.rau@typoplanet.de>
	 */
	private function loadClass($className) {
		$classNameParts = explode('_', $className,3);
		if ($classNameParts[0] === self::PACKAGE_PREFIX) {

				// Caches the $classFiles
			if (!is_array($this->classFiles[$classNameParts[1]]) || empty($this->classFiles[$classNameParts[1]])) {
				$this->classFiles[$classNameParts[1]] = $this->buildArrayOfClassFiles($classNameParts[1]);
				if (is_array($this->additionalIncludePaths)) {
					foreach ($this->additionalIncludePaths as $idx => $dir) {
						$temp = array();
						$temp = $this->buildArrayOfClassFiles($dir);
						$this->classFiles[$classNameParts[1]] = array_merge($temp, $this->classFiles[$classNameParts[1]]);
					}
				}
				t3lib_div::writeFileToTypo3tempDir($this->cacheFilePath, serialize($this->classFiles));
			}
			$classFilePathAndName = isset($this->classFiles[$classNameParts[1]][$className]) ? $this->classFiles[$classNameParts[1]][$className] : NULL;
			if (isset($classFilePathAndName) && file_exists($classFilePathAndName)) {
				require_once ($classFilePathAndName);
			}
		}
	}

	protected function getPackagePath($packageKey) {
		if (strpos($packageKey, '/') === FALSE && t3lib_extMgm::isLoaded(strtolower($packageKey))) {
			$path = t3lib_extMgm::extPath(strtolower($packageKey));
		} else {
			$path = t3lib_div::getFileAbsFileName($packageKey);
			if (substr($path,strlen($path) -1) !== '/') {
				$path .= '/';
			}
		}

		return $path;
	}
}
?>