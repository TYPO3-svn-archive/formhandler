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
 * A pre processor cleaning old files in the temporary upload folder if set.
 * 
 * Example:
 * <code>
 * preProcessors.1.class = Tx_Formhandler_PreProcessor_ClearTempFiles
 *
 * preProcessors.1.config.clearTempFilesOlderThan.value = 17
 * preProcessors.1.config.clearTempFilesOlderThan.unit = hours
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	PreProcessor
 */
class Tx_Formhandler_PreProcessor_ClearTempFiles extends Tx_Formhandler_AbstractPreProcessor {

	/**
	 * The main method called by the controller
	 *
	 * @param array $gp The GET/POST parameters
	 * @param array $settings The defined TypoScript settings for the finisher
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$this->olderThanValue = $this->settings['clearTempFilesOlderThan.']['value'];
		$this->olderThanUnit = $this->settings['clearTempFilesOlderThan.']['unit'];
		if (!empty($this->olderThanValue) && is_numeric($this->olderThanValue)) {
			$uploadFolder = Tx_Formhandler_StaticFuncs::getTempUploadFolder();
			$this->clearTempFiles($uploadFolder, $this->olderThanValue, $this->olderThanValue);
		}
		return $this->gp;
	}

	/**
	 * Deletes all files older than a specific time in a temporary upload folder.
	 * Settings for the threshold time and the folder are made in TypoScript.
	 *
	 * Here is an example:
	 * <code>
	 * plugin.Tx_Formhandler.settings.files.clearTempFilesOlderThanHours = 24
	 * plugin.Tx_Formhandler.settings.files.tmpUploadFolder = uploads/formhandler/tmp
	 * </code>
	 *
	 * @param integer $olderThan Delete files older than $olderThan hours.
	 * @return void
	 * @author	Reinhard Führicht <rf@typoheads.at>
	 */
	protected function clearTempFiles($uploadFolder, $olderThanValue, $olderThanUnit) {
		if (!$olderThanValue) {
			return;
		}

		//build absolute path to upload folder
		$path = Tx_Formhandler_StaticFuncs::getDocumentRoot() . $uploadFolder;

		//read files in directory
		$tmpFiles = t3lib_div::getFilesInDir($path);

		Tx_Formhandler_StaticFuncs::debugMessage('cleaning_temp_files', array($path));

		//calculate threshold timestamp
		//hours * 60 * 60 = millseconds
		$threshold = Tx_Formhandler_StaticFuncs::getTimestamp($olderThanValue, $olderThanUnit);

		//for all files in temp upload folder
		foreach ($tmpFiles as $idx => $file) {

			//if creation timestamp is lower than threshold timestamp
			//delete the file
			$creationTime = filemtime($path . $file);

			//fix for different timezones
			$creationTime += date('O') / 100 * 60;

			if ($creationTime < $threshold) {
				unlink($path . $file);
				Tx_Formhandler_StaticFuncs::debugMessage('deleting_file', array($file));
			}
		}
	}

}
?>
