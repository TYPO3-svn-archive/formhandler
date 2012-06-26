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
 * This finisher stores uploaded files by a user to a final folder. At the time this finisher is called, it is assured, that the form was fully submitted and valid.
 * Use this finisher to move the uploaded files to a save folder where they are not cleared by a possibly time based deletion.
 * This class needs a parameter "finishedUploadFolder" to be set in TS.
 *
 * Sample configuration:
 *
 * <code>
 * finishers.1.class = Tx_Formhandler_Finisher_StoreUploadedFiles
 * finishers.1.config.finishedUploadFolder = uploads/formhandler/finished/
 * finishers.1.config.renameScheme = [pid]_[filename]_[md5]_[time]_[marker1]_[marker2]
 * finishers.1.config.schemeMarkers.marker1 = Value
 * finishers.1.config.schemeMarkers.marker2 = TEXT
 * finishers.1.config.schemeMarkers.marker2.value = Textvalue
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_Finisher_StoreUploadedFiles extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		if ($this->settings['finishedUploadFolder']) {

			//move the uploaded files
			$this->moveUploadedFiles();
		}
		return $this->gp;
	}

	/**
	 * Moves uploaded files from temporary upload folder to a specified new folder.
	 * This enables you to move the files from a successful submission to another folder and clean the files in temporary upload folder from time to time.
	 *
	 * TypoScript example:
	 *
	 * 1. Set the temporary upload folder
	 * <code>
	 * plugin.Tx_Formhandler.settings.files.tmpUploadFolder = uploads/formhandler/tmp
	 * </code>
	 *
	 * 2. Set the folder to move the files to after submission
	 * <code>
	 * plugin.Tx_Formhandler.settings.finishers.1.class = Tx_Formhandler_Finisher_StoreUploadedFiles
	 * plugin.Tx_Formhandler.settings.finishers.1.config.finishedUploadFolder = uploads/formhandler/finishedFiles/
	 * plugin.Tx_Formhandler.settings.finishers.1.config.renameScheme = [filename]_[md5]_[time]
	 * </code>
	 *
	 * @return void
	 */
	protected function moveUploadedFiles() {

		$newFolder = $this->utilityFuncs->getSingle($this->settings, 'finishedUploadFolder');
		$newFolder = $this->utilityFuncs->sanitizePath($newFolder);
		$newFolder = $this->replaceSchemeMarkers($newFolder);
		$newFolder = $this->utilityFuncs->sanitizePath($newFolder);
		$uploadPath = $this->utilityFuncs->getDocumentRoot() . $newFolder;
		if(!file_exists($uploadPath)) {
			$doCreateNonExistingFolder = intval($this->utilityFuncs->getSingle($this->settings, 'createNonExistingFolder'));
			if(!isset($this->settings['createNonExistingFolder'])) {
				$doCreateNonExistingFolder = 1;
			}
			if($doCreateNonExistingFolder === 1) {
				t3lib_div::mkdir_deep($this->utilityFuncs->getDocumentRoot(), $newFolder);
				$this->utilityFuncs->debugMessage('Creating directory "' . $newFolder . '"');
			} else {
				$this->utilityFuncs->throwException('Directory "' . $newFolder . '" doesn\'t exist!');
			}
		}
		$sessionFiles = $this->globals->getSession()->get('files');
		if (is_array($sessionFiles) && !empty($sessionFiles) && strlen($newFolder) > 0) {
			foreach ($sessionFiles as $field => $files) {
				$this->gp[$field] = array();
				foreach ($files as $key => $file) {
					if ($file['uploaded_path'] != $uploadPath) {
						$newFilename = $this->getNewFilename($file['uploaded_name']);
						$filename = substr($newFilename, 0, strrpos($newFilename, '.'));
						$ext = substr($newFilename, strrpos($newFilename, '.'));

						$suffix = 1;

						//rename if exists
						while(file_exists($uploadPath . $newFilename)) {
							$newFilename = $filename . '_' . $suffix . $ext;
							$suffix++;
						}

						$this->utilityFuncs->debugMessage(
							'copy_file', 
							array(
								($file['uploaded_path'] . $file['uploaded_name']),
								($uploadPath . $newFilename)
							)
						);
						copy(($file['uploaded_path'] . $file['uploaded_name']), ($uploadPath . $newFilename));
						t3lib_div::fixPermissions($uploadPath . $newFilename);
						unlink(($file['uploaded_path'] . $file['uploaded_name']));
						$sessionFiles[$field][$key]['uploaded_path'] = $uploadPath;
						$sessionFiles[$field][$key]['uploaded_name'] = $newFilename;
						$sessionFiles[$field][$key]['uploaded_folder'] = $newFolder;
						$sessionFiles[$field][$key]['uploaded_url'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $newFolder . $newFilename;
						if (!is_array($this->gp[$field])) {
							$this->gp[$field] = array();
						}
						array_push($this->gp[$field], $newFilename);
					}
				}
			}
			$this->globals->getSession()->set('files', $sessionFiles);
		}
	}

	/**
	 * Generates a new filename for an uploaded file using settings in TypoScript.
	 *
	 * @param string The current filename
	 * @return string The new filename
	 **/
	protected function getNewFilename($oldName) {
		$fileparts = explode('.', $oldName);
		$fileext = '.'  .$fileparts[count($fileparts)-1];
		array_pop($fileparts);
		$filename = implode('.', $fileparts);

		$namingScheme = $this->utilityFuncs->getSingle($this->settings, 'renameScheme');
		if (!$namingScheme) {
			$namingScheme = '[filename]_[time]';
		}
		$newFilename = $namingScheme;
		$newFilename = str_replace('[filename]', $filename, $newFilename);
		$newFilename = str_replace('[time]', time(), $newFilename);
		$newFilename = str_replace('[md5]', md5($filename), $newFilename);
		$newFilename = str_replace('[pid]', $GLOBALS['TSFE']->id, $newFilename);
		$newFilename = $this->replaceSchemeMarkers($newFilename);

		//remove ',' from filename, would be handled as file separator 
		$newFilename = str_replace(',', '', $newFilename);
		$newFilename = $this->utilityFuncs->doFileNameReplace($newFilename);
		$newFilename .= $fileext;
		return $newFilename;
	}

	protected function replaceSchemeMarkers($str) {
		$replacedStr = $str;
		if (is_array($this->settings['schemeMarkers.'])) {
			foreach ($this->settings['schemeMarkers.'] as $markerName => $options) {
				if (!(strpos($markerName, '.') > 0)) {
					$value = $options;

					//use field value
					if (isset($this->settings['schemeMarkers.'][$markerName . '.']) && !strcmp($options, 'fieldValue')) {
						$value = $this->gp[$this->settings['schemeMarkers.'][$markerName . '.']['field']];
						if(is_array($value)) {
							$separator = $this->utilityFuncs->getSingle($this->settings['schemeMarkers.'][$markerName . '.'], 'separator');
							if(strlen($separator) === 0) {
								$separator = '-';
							}
							$value = implode($separator, $value);
						}
					} elseif (isset($this->settings['schemeMarkers.'][$markerName . '.'])) {
						$value = $this->utilityFuncs->getSingle($this->settings['schemeMarkers.'], $markerName);
					}
					$replacedStr = str_replace('[' . $markerName . ']', $value, $replacedStr);
				}
			}
		}
		return $replacedStr;
	}

}
?>
