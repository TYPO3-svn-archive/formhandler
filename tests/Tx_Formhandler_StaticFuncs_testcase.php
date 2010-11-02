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
 * Test for the Component "Tx_Formhandler_MarkerUtils" of the extension 'formhandler'
 *
 * @package	Tx_Formhandler
 * @subpackage	Tests
 */
class Tx_Formhandler_StaticFuncs_testcase extends tx_phpunit_testcase {

	protected $components;
	protected $repository;
		/**
	 *
	 * @var String
	 */
	protected $message = 'Tested value:';


	protected function setUp() {
		require_once(t3lib_extMgm::extPath('formhandler')."Classes/Utils/Tx_Formhandler_StaticFuncs.php");
		$GLOBALS['TSFE']->lang = 'en';
	}

	protected function tearDown() {
	}

	public function test_getDocumentRoot() {
		$this->assertEquals(Tx_Formhandler_StaticFuncs::getDocumentRoot(),t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT'));
	}

	public function test_getHostname() {
		$this->assertEquals(Tx_Formhandler_StaticFuncs::getHostname(),t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
	}

	public function test_sanitizePath() {
		$path = "fileadmin/test";
		$this->assertEquals(Tx_Formhandler_StaticFuncs::sanitizePath($path),"/fileadmin/test/");
		$path = "/fileadmin/test";
		$this->assertEquals(Tx_Formhandler_StaticFuncs::sanitizePath($path),"/fileadmin/test/");
		$path = "fileadmin/test/";
		$this->assertEquals(Tx_Formhandler_StaticFuncs::sanitizePath($path),"/fileadmin/test/");
		$path = "/fileadmin/test/example.html";
		$this->assertEquals(Tx_Formhandler_StaticFuncs::sanitizePath($path),"/fileadmin/test/example.html");
	}

	public function test_getFilledLangMarkers() {
		$fakeTemplate = '
			<div>###LLL:firstname###</div>
			<div>###LLL:lastname###</div>
		';

		$langFiles = array('EXT:formhandler/tests/locallang.xml');
		$langMarkers = Tx_Formhandler_StaticFuncs::getFilledLangMarkers($fakeTemplate,$langFiles);

		t3lib_div::debug($langMarkers, $this->message);

		$this->assertEquals(
		$langMarkers,
		array(
				"###LLL:firstname###" => "Firstname_translated",
//				"###LLL:FIRSTNAME###" => "Firstname_translated",
				"###LLL:lastname###" => "Lastname_translated",
//				"###LLL:LASTNAME###" => "Lastname_translated"
				));
	}

	public function test_getFilledValueMarkers() {
		$fakeGp = array();
		$fakeGp['firstname'] = "Test";
		$fakeGp['lastname'] = "Test";

		$markers = Tx_Formhandler_StaticFuncs::getFilledValueMarkers($fakeGp);

		$this->assertEquals($markers,array("###value_firstname###" => "Test","###value_lastname###" => "Test"));
	}

	public function test_removeUnfilledMarkers() {
		$fakeTemplate = '###LLL:firstname######error_sthg#####abcdef���!"�$$%&/###';
		$this->assertEquals(Tx_Formhandler_StaticFuncs::removeUnfilledMarkers($fakTemplate),"");
	}

	public function test_resolvePath() {
		$path = 'EXT:formhandler/Resources/PHP/fake.php';
		$resolvedPath = Tx_Formhandler_StaticFuncs::resolvePath($path);
		$expectedPath = Tx_Formhandler_StaticFuncs::getDocumentRoot().'/typo3conf/ext/formhandler/Resources/PHP/fake.php';
		$this->assertEquals($resolvedPath,$expectedPath);
	}

	public function test_resolveRelPath() {
		$path = 'EXT:formhandler/Resources/PHP/fake.php';
		$resolvedPath = Tx_Formhandler_StaticFuncs::resolveRelPath($path);
		$expectedPath = '../typo3conf/ext/formhandler/Resources/PHP/fake.php';
		$this->assertEquals($resolvedPath,$expectedPath);
	}
}
?>