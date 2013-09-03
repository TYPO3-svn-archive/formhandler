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
 * $Id:$
 *                                                                        */

/**
 * This PreProcessor adds the posibility to load default values from database.
 * Values for the first step are loaded to $gp values of other steps are stored
 * to the session.
 *
 * Example configuration:
 *
 * <code>
 * preProcessors.1.class = Tx_Formhandler_PreProcessor_LoadDB
 * #DB setup (properties commented out are not required).
 * #All properties can be processed as cObjects (like TEXT and COA)
 * preProcessors.1.config.select {
 *       #selectFields = *
 *       table = my_custom_table
 *       #where = 
 *       #groupBy = 
 *       #orderBy = 
 *       #limit = 
 * }
 * preProcessors.1.config.1.contact_via.mapping = email
 * preProcessors.1.config.2.[field1].mapping = listfield
 * preProcessors.1.config.2.[field1].separator = ,
 * #The following allows for dynamic field names
 * preProcessors.1.config.2.[field2].mapping {
 *       data = page:subtitle
 *       wrap = field_|_xyz
 * }
 * preProcessors.1.config.2.[field3].mapping < plugin.tx_exampleplugin
 * <code>
 *
 *
 * @author	Mathias Bolt Lesniak, LiliO Design <mathias@lilio.com>
 */

class Tx_Formhandler_PreProcessor_LoadDB extends Tx_Formhandler_AbstractPreProcessor {

	/**
	 * @var Array $data as associative array. Row data from DB.
	 * @access protected
	 */
	protected $data;

	/**
	 * Main method called by the controller
	 * 
	 * @return Array GP
	 */
	public function process() {
		$this->data = $this->loadDB($this->settings['select.']);

		foreach ($this->settings as $step => $stepSettings){
			$step = preg_replace('/\.$/', '', $step);

			if ($step !== 'select') {
				if (intval($step) === 1){
					$this->loadDBToGP($stepSettings);
				} elseif (is_numeric($step)) {
					$this->loadDBToSession($stepSettings, $step);
				}
			}
		}

		return $this->gp;
	}

	/**
	 * Loads data from DB intto the GP Array
	 *
	 * @return void
	 * @param array $settings
	 */
	protected function loadDBToGP($settings) {
		if (is_array($settings)) {
			$arrKeys = array_keys($settings);
			foreach ($arrKeys as $idx => $fieldname) {
				$fieldname = preg_replace('/\.$/', '', $fieldname);
				if (!isset($this->gp[$fieldname])) {
					$this->gp[$fieldname] = $this->parseValue($fieldname, $settings);
				}
			}
		}
	}

	/**
	 * Loads DB data into the Session. Used only for step 2+.
	 *
	 * @return void
	 * @param Array $settings
	 * @param int $step
	 */
	protected function loadDBToSession($settings, $step){
		session_start();
		if (is_array($settings) && $step) {
			$values = $this->globals->getSession()->get('values');
			$arrKeys = array_keys($settings);
			foreach ($arrKeys as $idx => $fieldname) {
				$fieldname = preg_replace('/\.$/', '', $fieldname);
				$values[$step][$fieldname] = $this->parseValue($fieldname, $settings);
			}
			$this->globals->getSession()->set('values', $values);
		}
	}

	protected function parseValue($fieldname, $settings) {
		$value = NULL;
		//pre process the field value.
		if (is_array($settings[$fieldname . '.']['preProcessing.'])) {
			$settings[$fieldname . '.']['preProcessing.']['value'] = $value;
			$value = $this->utilityFuncs->getSingle($settings[$fieldname . '.'], 'preProcessing');
		}

		if ($value === NULL) {
			$mapping = $this->utilityFuncs->getSingle($settings[$fN.'.'], 'mapping');
			if(isset($data[$mapping])) {
				$this->gp[$fN] = $data[$mapping];
			} else {
				$this->gp[$fN] = $this->utilityFuncs->getSingle($settings, $fN);
			}
			if ($settings[$fieldname . '.']['separator']) {
				$separator = $settings[$fieldname . '.']['separator'];
				$value = t3lib_div::trimExplode($separator, $value);
			}
		}

		//post process the field value.
		if (is_array($settings[$fieldname . '.']['postProcessing.'])) {
			$settings[$fieldname . '.']['postProcessing.']['value'] = $value;
			$value = $this->utilityFuncs->getSingle($settings[$fieldname . '.'], 'postProcessing');
		}
		
		if(isset($settings[$fieldname . '.']['type']) && $this->utilityFuncs->getSingle($settings[$fieldname . '.'], 'type') === 'upload') {
			if(!$files) {
				$files = array();
			}
			$files[$fieldname] = array();
			if(!empty($value)) {
				$uploadPath = $this->utilityFuncs->getTempUploadFolder($fieldname);
				$filesArray = $value;
				if(!is_array($filesArray)) {
					$filesArray = t3lib_div::trimExplode(',', $value);
				}

				foreach($filesArray as $k => $uploadFile) {
					if(strpos($uploadFile, '/') !== FALSE) {
						$file = PATH_site . $uploadFile;
						$uploadedUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $uploadFile;
					} else {
						$file = PATH_site . $uploadPath . $uploadFile;
						$uploadedUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $uploadPath . $uploadFile;
					}
					
					$uploadedUrl = str_replace('//', '/', $uploadedUrl);
					$files[$fieldname][] = array (
						'name' => $uploadFile,
						'uploaded_name' => $uploadFile,
						'uploaded_path' => PATH_site . $uploadPath,
						'uploaded_folder' => $uploadPath,
						'uploaded_url' => $uploadedUrl,
						'size' => filesize($file)
					);
				}
				$this->globals->getSession()->set('files', $files);
			}
		}
		return $value;
	}

	/**
	 * Loads data from DB
	 *
	 * @return Array of row data
	 * @param Array $settings
	 * @param int $step
	 */
	protected function loadDB($settings) {
		$selectFields = $this->utilityFuncs->getSingle($settings, 'selectFields');
		if(strlen($selectFields) === 0) {
			$selectFields = '*';
		}

		$sql = $GLOBALS['TYPO3_DB']->SELECTquery(
			$selectFields,
			$this->utilityFuncs->getSingle($settings, 'table'),
			$this->utilityFuncs->getSingle($settings, 'where'),
			$this->utilityFuncs->getSingle($settings, 'groupBy'),
			$this->utilityFuncs->getSingle($settings, 'orderBy'),
			$this->utilityFuncs->getSingle($settings, 'limit')
		);

		$this->utilityFuncs->debugMessage($sql);

		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
		$rowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($rowCount === 1) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			return $row;
		} elseif($rowCount > 0) {
			$this->utilityFuncs->debugMessage('sql_too_many_rows', array($rowCount), 3);
		}
		return array();
	}
}

?>