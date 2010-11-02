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

require_once (t3lib_extMgm::extPath('formhandler') . 'Classes/Component/Tx_Formhandler_Component_Manager.php');

/**
 * Test for the Component "Tx_Formhandler_Logger_DB" of the extension 'formhandler'
 *
 * @package	Tx_Formhandler
 * @subpackage	Tests
 */
class Tx_Formhandler_Logger_DB_testcase extends tx_phpunit_testcase {

	protected $components;
	protected $logger;

	protected function setUp() {
		$this->componentManager = Tx_Formhandler_Component_Manager::getInstance();
		$this->logger = $this->componentManager->getComponent("Tx_Formhandler_Logger_DB");
		$GLOBALS['TSFE']->initFEuser();
	}

	protected function tearDown() {
		unset($this->logger);
		unset($this->componentManager);
	}

	public function test_log() {
		$fakeGp = array();
		$fakeGp['firstname'] = "Test";
		$fakeGp['lastname'] = "Test";
		$hash = hash("md5",serialize(array_keys($fakeGp)));
		$this->logger->init($fakeGp,array("nodebug"=>true));
		$this->logger->process();
		$currTime = time();
		$threshold = $currTime - 2000;
		$lastId = Tx_Formhandler_Session::get('inserted_uid');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_formhandler_log","uid=".$lastId);
		$this->assertNotNull($res,'Logged record can be selected again');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_formhandler_log","uid=".$lastId);
		$row['params'] = unserialize($row['params']);
		$this->assertEquals($row['params'],array("firstname"=>"Test","lastname"=>"Test"));
		$this->assertEquals($row['ip'],t3lib_div::getIndpEnv('REMOTE_ADDR'));
		$this->assertEquals($row['key_hash'],$hash);
		$this->assertGreaterThanOrEqual($threshold,(int)$row['crdate']);
		$this->assertGreaterThanOrEqual($threshold,(int)$row['tstamp']);
		$this->assertType("int",(int)$row['pid']);
		$this->assertGreaterThanOrEqual((int)$row['pid'],0);

	}


}
?>