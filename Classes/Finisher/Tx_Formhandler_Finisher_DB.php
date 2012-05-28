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
 * This finisher stores the submitted values into a table in the TYPO3 database according to the configuration
 *
 * Example configuration:
 *
 * <code>
 * finishers.1.class = Tx_Formhandler_Finisher_DB
 *
 * #The table to store the records in
 * finishers.1.config.table = tt_content
 *
 * #The uid field. Default: uid
 * finishers.1.config.key = uid
 *
 * #Do not insert the record, but update an existing one.
 * #The uid of the existing record must exist in Get/Post
 * finishers.1.config.updateInsteadOfInsert = 1
 *
 * #map a form field to a db field.
 * finishers.1.config.fields.header.mapping = name
 *
 * #if form field is empty, insert this
 * finishers.1.config.fields.header.if_is_empty = None given
 * finishers.1.config.fields.bodytext.mapping = interests
 *
 * #if form field is an array, implode using this separator. Default: ,
 * finishers.1.config.fields.bodytext.separator = ,
 *
 * #add static values for some fields
 * finishers.1.config.fields.hidden = 1
 * finishers.1.config.fields.pid = 39
 *
 * #add special values
 * finishers.1.config.fields.subheader.special = sub_datetime
 * finishers.1.config.fields.crdate.special = sub_tstamp
 * finishers.1.config.fields.tstamp.special = sub_tstamp
 * finishers.1.config.fields.imagecaption.special = ip
 * </code>
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 */
class Tx_Formhandler_Finisher_DB extends Tx_Formhandler_AbstractFinisher {

	/**
	 * The name of the table to put the values into.
	 *
	 * @access protected
	 * @var string
	 */
	protected $table;

	/**
	 * The field in the table holding the primary key.
	 *
	 * @access protected
	 * @var string
	 */
	protected $key;

	/**
	 * A flag to indicate if to insert the record or to update an existing one
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $doUpdate;

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		$evaluation = TRUE;
		if (isset($this->settings['condition'])) {
			$condition = $this->parseCondition($this->utilityFuncs->getSingle($this->settings, 'condition'));
			eval('$evaluation = ' . $condition . ';');
			$evaluationMessage = ($evaluation === TRUE) ?  'TRUE' : 'FALSE';
			$this->utilityFuncs->debugMessage('condition', array($evaluationMessage, $condition));
		}

		if ($evaluation) {
			$this->utilityFuncs->debugMessage('data_stored');

			//set fields to insert/update
			$queryFields = $this->parseFields();

			//query the database
			$isSuccess = $this->save($queryFields);

			if (!is_array($this->gp['saveDB'])) {
				$this->gp['saveDB'] = array();
			}

			//Store info in GP only if the query was successful
			if($isSuccess) {

				//Get DB info, including UID
				if (!$this->doUpdate) {
					$this->gp['inserted_uid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
					$this->gp[$this->table . '_inserted_uid'] = $this->gp['inserted_uid'];
					$info = array(
						'table' => $this->table,
						'uid' => $this->gp['inserted_uid'],
						'uidField' => $this->key
					);
					array_push($this->gp['saveDB'], $info);
				} else {
					$uid = $this->getUpdateUid();
					$info = array(
						'table' => $this->table,
						'uid' => $uid,
						'uidField' => $this->key
					);
					array_push($this->gp['saveDB'], $info);
				}
	
				//Insert the data written to DB into GP array
				$dataKeyName = $this->table;
				$dataKeyIndex = 1;
				while(isset($this->gp['saveDB'][$dataKeyName])) {
					$dataKeyIndex++;
					$dataKeyName = $this->table . '_' . $dataKeyIndex;
				}
				$this->gp['saveDB'][$dataKeyName] = $queryFields;
			}
		}

		return $this->gp;
	}

	/**
	 * Parses condition statement given by the typoscript configuration. Replaces field with real content for a proper PHP eval().
	 *
	 * @author	Fabien Udriot <fabien.udriot@ecodev.ch>
	 * @param	string	$condition: the condition given by the typoscript configuration.
	 * @return	string	$condition: the condition formated for evaluation.
	 */
	protected function parseCondition($condition) {
		$pattern = '/[\w]+/is';
		preg_match_all($pattern, $condition, $fields);
		foreach ($fields[0] as $idx => $fieldName) {
			if (isset($this->gp[$fieldName])) {

				// Formats the value by surrounding it with '.
				$value = "'" . str_replace("'", "\'", $this->gp[$fieldName]) . "'";

				// Replace conditions
				$condition = str_replace($fieldName, $value, $condition);
			} else {
				$condition = str_replace($fieldName, 'FALSE', $condition);
			}
		}
		return $condition;
	}

	/**
	 * Method to query the database making an insert or update statement using the given fields.
	 *
	 * @param array &$queryFields Array holding the query fields
	 * @return boolean Success flag
	 */
	protected function save(&$queryFields) {

		$isSuccess = FALSE;

		//insert query
		if (!$this->doUpdate) {
			$isSuccess = $this->doInsert($queryFields);

			//update query
		} else {

			//check if uid of record to update is in GP
			$uid = $this->getUpdateUid();

			$andWhere = $this->utilityFuncs->getSingle($this->settings, 'andWhere');
			$isSuccess = $this->doUpdate($uid, $queryFields, $andWhere);
		}
		return $isSuccess;
	}

	protected function doesRecordExist($uid) {
		$exists = FALSE;
		if($uid) {
			$uid = $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $this->table);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($this->key, $this->table, $this->key . '=' . $uid);
			if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
				$exists = TRUE;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $exists;
	}

	protected function doInsert($queryFields) {
		$isSuccess = TRUE;
		$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->table, $queryFields);
		$this->utilityFuncs->debugMessage('sql_request', array($query));
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_error()) {
			$isSuccess = FALSE;
			$this->utilityFuncs->debugMessage('error', array($GLOBALS['TYPO3_DB']->sql_error()), 3);
		}
		return $isSuccess;
	}

	protected function doUpdate($uid, $queryFields, $andWhere) {
		$isSuccess = TRUE;
		$uid = $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $this->table);
		$andWhere = trim($andWhere);
		if(substr($andWhere, 0, 3) === 'AND') {
			$andWhere = trim(substr($andWhere, 3));
		}
		if(strlen($andWhere) > 0) {
			$andWhere = ' AND ' . $andWhere;
		}
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery($this->table, $this->key . '=' . $uid . $andWhere, $queryFields);
		$this->utilityFuncs->debugMessage('sql_request', array($query));
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if($GLOBALS['TYPO3_DB']->sql_error()) {
			$isSuccess = FALSE;
			$this->utilityFuncs->debugMessage('error', array($GLOBALS['TYPO3_DB']->sql_error()), 3);
		}
		return $isSuccess;
	}

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @return void
	 */
	public function init($gp, $settings) {
		parent::init($gp, $settings);

		//set table
		$this->table = $this->utilityFuncs->getSingle($this->settings, 'table');
		if (!$this->table) {
			$this->utilityFuncs->throwException('no_table', 'Tx_Formhandler_Finisher_DB');
			return;
		}
		
		if(!is_array($this->settings['fields.'])) {
			$this->utilityFuncs->throwException('no_fields', 'Tx_Formhandler_Finisher_DB');
			return;
		}

		//set primary key field
		$this->key = $this->utilityFuncs->getSingle($this->settings, 'key');
		if (strlen($this->key) === 0) {
			$this->key = 'uid';
		}

		//check whether to update or to insert a record
		$this->doUpdate = FALSE;
		if (intval($this->utilityFuncs->getSingle($this->settings, 'updateInsteadOfInsert')) === 1) {

			//check if uid of record to update is in GP
			$uid = $this->getUpdateUid();

			$recordExists = $this->doesRecordExist($uid);
			if ($recordExists) {
				$this->doUpdate = TRUE;
			} elseif(intval($this->utilityFuncs->getSingle($this->settings, 'insertIfNoUpdatePossible')) !== 1) {
				$this->utilityFuncs->debugMessage('no_update_possible', array(), 2);
			}
		}
	}

	/**
	 * Parses mapping settings and builds an array holding the query fields information.
	 *
	 * @return array The query fields
	 */
	protected function parseFields() {
		$queryFields = array();

		//parse mapping
		foreach ($this->settings['fields.'] as $fieldname => $options) {
			$fieldname = str_replace('.', '', $fieldname);
			if (isset($options) && is_array($options)) {
				if(!isset($options['special'])) {
					$mapping = $options['mapping'];

					//if no mapping default to the name of the form field
					if (!$mapping) {
						$mapping = $fieldname;
					}

					$fieldValue = $this->gp[$mapping];

					//pre process the field value. e.g. to format a date
					if (isset($options['preProcessing.']) && is_array($options['preProcessing.'])) {
						if(!isset($options['preProcessing.']['value'])) {
							$options['preProcessing.']['value'] = $fieldValue;
						}
						$fieldValue = $this->utilityFuncs->getSingle($options, 'preProcessing');
					}

					if (isset($options['mapping.']) && is_array($options['mapping.'])) {
						if(!isset($options['mapping.']['value'])) {
							$options['mapping.']['value'] = $fieldValue;
						}
						$fieldValue = $this->utilityFuncs->getSingle($options, 'mapping');
					}

					//process empty value handling
					if ($options['ifIsEmpty'] && strlen($fieldValue) == 0) {
						$fieldValue = $this->utilityFuncs->getSingle($options, 'ifIsEmpty');
					}

					if (intval($this->utilityFuncs->getSingle($options, 'zeroIfEmpty')) === 1 && strlen($fieldValue) == 0) {
						$fieldValue = 0;
					}

					//process array handling
					if (is_array($fieldValue)) {
						$separator = ',';
						if ($options['separator']) {
							$separator = $this->utilityFuncs->getSingle($options, 'separator');
						}
						$fieldValue = implode($separator, $fieldValue);
					}

					//process uploaded files
					$files = $this->globals->getSession()->get('files');
					if (isset($files[$fieldname]) && is_array($files[$fieldname])) {
						$fieldValue = $this->getFileList($files, $fieldname);
					}
				} else {
					switch ($options['special']) {
						case 'sub_datetime':
							$dateFormat = 'Y-m-d H:i:s';
							if($options['special.']['dateFormat']) {
								$dateFormat = $this->utilityFuncs->getSingle($options['special.'], 'dateFormat');
							}
							$fieldValue = date($dateFormat, time());
							break;
						case 'sub_tstamp':
							$fieldValue = time();
							break;
						case 'ip':
							$fieldValue = t3lib_div::getIndpEnv('REMOTE_ADDR');
							break;
						case 'inserted_uid':
							$table = $this->utilityFuncs->getSingle($options['special.'], 'table');
							if (is_array($this->gp['saveDB'])) {
								foreach ($this->gp['saveDB'] as $idx => $info) {
									if ($info['table'] === $table) {
										$fieldValue = $info['uid'];
									}
								}
							}
							break;
					}
				}
			} else {
				$fieldValue = $options;
			}

			//post process the field value after formhandler did it's magic.
			if (is_array($options['postProcessing.'])) {
				if(!isset($options['postProcessing.']['value'])) {
					$options['postProcessing.']['value'] = $fieldValue;
				}
				$fieldValue = $this->utilityFuncs->getSingle($options, 'postProcessing');
			}

			$queryFields[$fieldname] = $fieldValue;

			if (intval($this->utilityFuncs->getSingle($options, 'nullIfEmpty')) === 1 && strlen($queryFields[$fieldname]) == 0) {
				unset($queryFields[$fieldname]);
			}
		}
		return $queryFields;
	}

	/**
	 * returns a list of uploaded files from given field.
	 * @return string list of filenames
	 * @param string $fieldname
	 */
	protected function getFileList($files, $fieldname){
		$filenames = array();
		foreach ($files[$fieldname] as $idx => $file) {
			array_push($filenames, $file['uploaded_name']);
		}
		return implode(',', $filenames);
	}

	/**
	 * Returns current UID to use for updating the DB.
	 * @return int UID
	 */
	protected function getUpdateUid() {
		$uid = $this->utilityFuncs->getSingle($this->settings, 'key_value');
		$disableFallback = (intval($this->utilityFuncs->getSingle($this->settings, 'disableUpdateUidFallback')) === 1);
		if(!$disableFallback) {
			if (!$uid) {
				$uid = $this->gp[$this->key];
			}
			if (!$uid) {
				$uid = $this->gp['inserted_uid'];
			}
		}
		return $uid;
	}

}
?>
