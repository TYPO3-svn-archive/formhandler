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
 * Abstract class for Controller Classes used by Formhandler.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Controller
 * @abstract
 */
abstract class Tx_Formhandler_AbstractController implements Tx_Formhandler_ControllerInterface {

	/**
	 * The content returned by the controller
	 *
	 * @access protected
	 * @var Tx_Formhandler_Content
	 */
	protected $content;

	/**
	 * The key of a possibly selected predefined form
	 *
	 * @access protected
	 * @var string
	 */
	protected $predefined;

	/**
	 * The path to a possibly selected translation file
	 *
	 * @access protected
	 * @var string
	 */
	protected $langFile;

	/**
	 * Sets the content attribute of the controller
	 *
	 * @param Tx_Formhandler_Content $content
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Returns the content attribute of the controller
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @return Tx_Formhandler_Content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Sets the internal attribute "predefined"
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @param string $key
	 * @return void
	 */
	public function setPredefined($key) {
		$this->predefined = $key;
	}

	/**
	 * Sets the internal attribute "redirectPage"
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @param integer $new
	 * @return void
	 */
	public function setRedirectPage($new) {
		$this->redirectPage = $new;
	}

	/**
	 * Sets the internal attribute "requiredFields"
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @param array $new
	 * @return void
	 */
	public function setRequiredFields($new) {
		$this->requiredFields = $new;
	}

	/**
	 * Sets the internal attribute "langFile"
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @param array $langFiles
	 * @return void
	 */
	public function setLangFiles($langFiles) {
		$this->langFiles = $langFiles;
	}

	/**
	 * Sets the internal attribute "emailSettings"
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @param array $new
	 * @return void
	 */
	public function setEmailSettings($new) {
		$this->emailSettings = $new;
	}

	/**
	 * Sets the template file attribute to $template
	 *
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 * @param string $template
	 * @return void
	 */
	public function setTemplateFile($template) {
		$this->templateFile = $template;
	}

	/**
	 * Returns the right settings for the formhandler (Checks if predefined form was selected)
	 *
	 * @author Reinhard Führicht <rf@typoheads.at>
	 * @return array The settings
	 */
	public function getSettings() {
		$settings = $this->configuration->getSettings();
		if ($this->predefined && is_array($settings['predef.'][$this->predefined])) {
			$predefSettings = $settings['predef.'][$this->predefined];
			unset($settings['predef.'][$this->predefined]);
			$settings = t3lib_div::array_merge_recursive_overrule($settings, $predefSettings);
		}
		
		if($settings['packageConfig.']) {
			$settings = $this->parsePackageConfig($settings);
		}
		return $settings;
	}
	
	protected function parsePackageConfig($settings) {
		if($settings['packageConfig.']['modules']) {
			$modules = t3lib_div::trimExplode(',', $settings['packageConfig.']['modules']);
			
			$theme = $settings['packageConfig.']['theme'];
			if(!$theme) {
				$theme = 'default';
			}
			foreach($modules as $module) {
				if(file_exists(t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/fileadmin/formkits/modules/' . $module)) {
					if(is_array($GLOBALS['TSFE']->tmpl->setup['lib.']['formhandlerModules.'][$module . '.'])) {
						$settings = $this->cObj->joinTSarrays($GLOBALS['TSFE']->tmpl->setup['lib.']['formhandlerModules.'][$module . '.'], $settings);
					}
					if(file_exists(t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/fileadmin/formkits/modules/' . $module . '/themes/' . $theme . '/css/styles.css')) {
						$settings['cssFile.'][$module] = 'fileadmin/formkits/modules/' . $module . '/themes/' . $theme . '/css/styles.css';
					} else {
						$settings['cssFile.'][$module] = 'fileadmin/formkits/modules/' . $module . '/themes/default/css/styles.css';
					}
					
					if(is_array($GLOBALS['TSFE']->tmpl->setup['lib.']['formhandlerModules.'][$module . '.']['additionalIncludePaths.'])) {
						foreach($GLOBALS['TSFE']->tmpl->setup['lib.']['formhandlerModules.'][$module . '.']['additionalIncludePaths.'] as $path) {
							Tx_Formhandler_Component_Manager::getInstance()->addIncludePath($path);
						}
					}
					
				} else {
					Tx_Formhandler_StaticFuncs::throwException('Module ' . $module . ' not found!');
				}
			}
		}
		unset($settings['predef.']);
		return $settings;
	}
}
?>
