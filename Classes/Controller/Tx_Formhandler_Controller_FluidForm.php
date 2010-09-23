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
 * $Id$
 *                                                                        */

/**
 * We need very few changes to some formhandler-mechanisms 
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 */
class Tx_Formhandler_Controller_FluidForm extends Tx_Formhandler_Controller_Form
{
	/**
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected static $controllerContext;
	
	/**
	 * We trick formhandler and put it across we have template-string
	 * @see Tx_Formhandler_StaticFuncs#readTemplateFile()
	 */
	protected function init()
	{		
		$this->templateFile = "-\n-";
		parent::init();
		
		if (!$this->view instanceof Tx_Formhandler_View_Fluid)
		{
			throw new Exception(__CLASS__.' needs an instance of Tx_Formhandler_View_Fluid as view!');
		}
	}
	
	/**
	 * Usually the controller sets the controllerContext on the view - in formhandler
	 * we need the view to pull it from here because some views are instantiated from
	 * without this controller (e.g. Tx_Formhandler_View_FluidMail)
	 * 
	 * @return Tx_Extbase_MVC_Controller_ControllerContext
	 */
	public static function getControllerContext()
	{
		if (!self::$controllerContext)
		{
			/* @var $request Tx_Extbase_MVC_Web_Request */
    		$request = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Request');
    		$request->setControllerExtensionName('formhandler');
    		$request->setPluginName('pi1');
    		$request->setControllerName('Form');
    		$request->setFormat('html');
    		$request->setBaseURI(t3lib_div::locationHeaderUrl(''));
    		
    		/* @var $uriBuilder Tx_Extbase_MVC_Web_Routing_UriBuilder */
    		$uriBuilder = t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Routing_UriBuilder');
    		$uriBuilder->setRequest($request);
    		$uriBuilder->setNoCache(true);
    		
    		self::$controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
    		self::$controllerContext->setRequest($request);
    		self::$controllerContext->setUriBuilder($uriBuilder);
		}
		
		return self::$controllerContext;
	}
	
	protected function getStepInformation()
	{
		parent::getStepInformation();
	}
	
	public function getSettings()
	{
		$settings = parent::getSettings();
		if(empty($settings['view'])) {
			$settings['view'] = 'Tx_Formhandler_View_Fluid';
		}
		return $settings;
	}
}