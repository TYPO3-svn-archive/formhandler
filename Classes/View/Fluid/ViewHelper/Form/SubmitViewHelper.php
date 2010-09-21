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
 * This helper renders the submit buttons that are needed to tell formhandler
 * which action to do next.
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_Formhandler_Fluid_ViewHelper_Form_SubmitViewHelper extends Tx_Fluid_ViewHelpers_Form_SubmitViewHelper
{
	/**
	 * Renders the submit button.
	 *
	 * @return string
	 */
	public function render()
	{
		$this->tag->addAttribute('type', 'submit');
		$this->tag->addAttribute('value', $this->getValue());
		$this->tag->addAttribute('name', $this->getName());

		return $this->tag->render();
	}
	
	/**
	 * Finds the name out of the action-argument of this helper
	 * 
	 * @return string The name for the desired action 
	 */
	protected function getName()
	{
		$name = array('step');
		switch ($this->arguments['action'])
		{
			case 'reload':
				$name['step']	= Tx_Formhandler_Session::get('currentStep');
				$name['action']	= 'reload';
				break;
			case 'prev':
				$name['step']	= Tx_Formhandler_Session::get('currentStep') - 1;
				$name['action']	= 'prev';
				break;
			case 'next':
			default:
				$name['step']	= Tx_Formhandler_Session::get('currentStep') + 1;
				$name['action']	= 'next';
		}		
		return $this->prefixFieldName(implode('-', $name));
	}
}