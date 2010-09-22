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
 * This helper overrides the hidden fields of the default form-helper and
 * takes the formValuesPrefix of formhandler into account
 * 
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	View_Fluid_ViewHelper
 */
class Tx_Formhandler_Fluid_ViewHelper_FormViewHelper extends Tx_Fluid_ViewHelpers_FormViewHelper
{
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#renderHiddenReferrerFields()
	 */
	protected function renderHiddenReferrerFields()
	{
		$randomID = Tx_Formhandler_Globals::$randomID;
		
		$hiddenFields = '
		<input type="hidden" name="no_cache" value="1" />
		<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'" />
		<input type="hidden" name="'.$this->prefixFieldName('submitted').'" value="1" />
		<input type="hidden" name="'.$this->prefixFieldName('randomID').'" value="'.$randomID.'" />
		<input type="hidden" id="removeFile-'.$randomID.'" name="'.$this->prefixFieldName('removeFile').'" value="" />
		<input type="hidden" id="removeFileField-'.$randomID.'" name="'.$this->prefixFieldName('removeFileField').'" value="" />
		<input type="hidden" id="submitField-'.$randomID.'" name="'.$this->prefixFieldName('submitField').'" value="" />';
		
		return $hiddenFields;
	}
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#getFieldNamePrefix()
	 */
	protected function getFieldNamePrefix() {
		return (string) Tx_Formhandler_Globals::$formValuesPrefix;
	}
	
	/**
	 * Trigger ObjectAccessorMode
	 * @see Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper#isObjectAccessorMode()
	 */
	protected function addFormNameToViewHelperVariableContainer()
	{
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$this->viewHelperVariableContainer->add('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName', '');
		}
	}

	/**
	 * Removes the "form name" from the ViewHelperVariableContainer.
	 */
	protected function removeFormNameFromViewHelperVariableContainer()
	{
		if (Tx_Formhandler_Globals::$formValuesPrefix) {
			$this->viewHelperVariableContainer->remove('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName');
		}
	}
	
	/* (non-PHPdoc)
	 * @see typo3/sysext/fluid/Classes/ViewHelpers/Tx_Fluid_ViewHelpers_FormViewHelper#renderRequestHashField()
	 */
	protected function renderRequestHashField() {
		return '';
	}
}