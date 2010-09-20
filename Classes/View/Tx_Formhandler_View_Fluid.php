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
	protected $renderer;
	
	public function render($gp, $errors)
	{
		$this->renderer = t3lib_div::makeInstance('Tx_Fluid_View_TemplateView');
		$controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
		$controllerContext->setRequest(t3lib_div::makeInstance('Tx_Extbase_MVC_Request'));
		$this->renderer->setControllerContext($controllerContext);
				
		$this->renderer->setTemplatePathAndFilename('fileadmin/template/form/index.html');
		
		$this->renderer->assignMultiple(
			array(
				'gp'		=> $gp,
				'errors'	=> $errors
			)
		);
		
		$this->fillDefaultMarkers();
		
		return $this->renderer->render();
	}
	
	protected function fillDefaultMarkers()
	{
		$this->renderer->assignMultiple(
			array(
				'timestamp'	=> time(),
				'randomId'	=> Tx_Formhandler_Globals::$randomID,
				'relUrl' 	=> $path = $this->getUrl(),
				'absUrl'	=> t3lib_div::locationHeaderUrl('').$path,
			)
		);
	}
	
	protected function getUrl()
	{
		$parameters = t3lib_div::_GET();
		if (isset($parameters['id'])) {
			unset($parameters['id']);
		}
		
		$path = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $parameters);
		$path = str_replace('&', '&amp;', $path);
		
		return $path;
	}
}