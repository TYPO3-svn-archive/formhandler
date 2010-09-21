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
 *
 * $Id: Tx_Formhandler_View_Form.php 36528 2010-08-09 13:04:41Z reinhardfuehricht $
 *                                                                        */

// Somehow autoloading from Fluid does not work yet :/
require_once t3lib_extMgm::extPath('formhandler') . 'Classes/View/Fluid/ViewHelper/FormViewHelper.php';
require_once t3lib_extMgm::extPath('formhandler') . 'Classes/View/Fluid/ViewHelper/Form/SubmitViewHelper.php';

/**
 * A default view for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	View
 */
class Tx_Formhandler_View_Fluid extends Tx_Formhandler_AbstractView
{	
	/**
	 * @var Tx_Fluid_View_TemplateView
	 */
	protected $view;
	
	/**
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected $controllerContext;
	
	/**
	 * @var Tx_Extbase_MVC_Request
	 */
	protected $request;
	
	/**
	 * @var Tx_Fluid_Compatibility_ObjectManager
	 */
	protected $objectManager;
	
	public function initializeView()
	{
		$this->objectManager = t3lib_div::makeInstance('Tx_Fluid_Compatibility_ObjectManager');
		
		$this->view = t3lib_div::makeInstance('Tx_Fluid_View_TemplateView');
		$this->controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
		$this->request = t3lib_div::makeInstance('Tx_Extbase_MVC_Request');
		
		$this->request->setControllerExtensionName('formhandler');
		$this->request->setPluginName('pi1');
		
		$this->controllerContext->setRequest($this->request);
		//$this->controllerContext
		$this->view->setControllerContext($this->controllerContext);
	}
	
	/**
	 * Get the path to the template to set it as fluid template
	 * 
	 * @return string
	 */
	protected function getTemplatePath()
	{
		$settings = Tx_Formhandler_Globals::$settings;
		
		if ($settings['templateFile'])
		{
			$path = Tx_Formhandler_StaticFuncs::resolvePath($settings['templateFile']);
		}

		if(!$path) {
			
			Tx_Formhandler_StaticFuncs::throwException('no_template_file');
		}
		return $path;
	}
	
	public function render($gp, $errors)
	{				
		$this->view->setTemplatePathAndFilename($this->getTemplatePath());
		
		$this->view->assign('gp', $gp);
		$this->view->assign('errors', $errors);
		
		$this->assignDefaults();
		
		return $this->view->render();
	}
	
	protected function assignDefaults()
	{
		$this->view->assignMultiple(
			array(
				'timestamp'			=> time(),
				'submission_date'	=> date('d.m.Y H:i:s', time()),
				'randomId'			=> Tx_Formhandler_Globals::$randomID,
				'relUrl' 			=> $url = $this->getUrl(),
				'absUrl'			=> t3lib_div::locationHeaderUrl('').$url,
				'fieldNamePrefix'	=> Tx_Formhandler_Globals::$formValuesPrefix,
				'ip'				=> t3lib_div::getIndpEnv('REMOTE_ADDR'),
				'pid'				=> $GLOBALS['TSFE']->id,
				'currentStep'		=> Tx_Formhandler_Session::get('currentStep'),
				'totalSteps'		=> Tx_Formhandler_Session::get('totalSteps'),
				'lastStep'			=> Tx_Formhandler_Session::get('lastStep')
			)
		);
		
		if ($this->gp['generated_authCode']) {
			$this->view->assign('authCode', $this->gp['generated_authCode']);
		}
		
		/*
		Stepbar currently removed - probably this should move in a partial
		$markers['###step_bar###'] = $this->createStepBar(
			Tx_Formhandler_Session::get('currentStep'),
			Tx_Formhandler_Session::get('totalSteps'),
			$prevName,
			$nextName
		);
		*/
		
		/*
		Not yet realized
		$this->fillCaptchaMarkers($markers);
		$this->fillFEUserMarkers($markers);
		$this->fillFileMarkers($markers);
		*/
	}
	
	protected function getUrl()
	{
		$parameters = t3lib_div::_GET();
		if (isset($parameters['id'])) {
			unset($parameters['id']);
		}
		
		$url = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $parameters);
		$url = str_replace('&', '&amp;', $url);
		
		return $url;
	}
}