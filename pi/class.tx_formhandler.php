<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

require_once (t3lib_extMgm::extPath('formhandler') . 'Classes/Controller/Tx_Formhandler_Dispatcher.php');

/**
 * The plugin class that's registered in TCA and calls the dispatcher
 * TypoScript object: plugin.tx_formhandler
 *
 * @author Christian Opitz <co@netzelf.de>
 */
class tx_formhandler extends Tx_Formhandler_Dispatcher {
	
	public $prefixId      = 'tx_formhandler';  // Same as class name
	
	public $scriptRelPath = 'pi/class.tx_formhandler.php'; // Path to this script relative to the extension dir.
	
	public $extKey        = 'formhandler'; // The extension key.
	
	public $pi_checkCHash = true;
	
	public $conf;
	
	/**
	 * preloads the setup and calls parent::main
	 *
	 * @see typo3conf/ext/formhandler/Classes/Controller/Tx_Formhandler_Dispatcher#main()
	 */
	public function main($content, $setup) {
		
		$GLOBALS['TSFE']->tmpl->setup['plugin.']['Tx_Formhandler.'] = $setup;

		$result = parent::main($content, $setup);
		
		return $result;
	}
}
?>