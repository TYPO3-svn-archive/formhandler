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
 * When a BE-user is logged in and autoCreate is to true this looks if
 * the specified table exists and if not create it with the key-field (uid).
 * Then it syncs the DB-fields with the fields found in the form with help
 * of template parser (#9446).
 * 
 * Independent from that it will map all form fields that are'nt submits to 
 * DB-fields, expecting the field names are the same. This is only done for 
 * fields that are not manually mapped. 
 *
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	Finisher
 */
class Tx_Formhandler_Finisher_AutoDB extends Tx_Formhandler_Finisher_DB
{
	/**
	 * The name of the table to put the values into.
	 * @todo Make it protected var in Tx_Formhandler_AbstractFinisher
	 * @var string
	 */
	public $settings;
	
	/**
	 * @var t3lib_db
	 */
	protected $db;
	
	/**
	 * @var string Attributes for new db fields
	 */
	protected $newFieldsSqlAttribs = 'TINYTEXT NOT NULL';
	
	public function init($gp, $settings)
	{
		if (!is_array($settings['fields.']))
		{
			$settings['fields.'] = array();
		}
		$this->settings = $settings;
		parent::init($gp, $settings);
		
		$this->db = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Generates mapping settings
	 *
	 * @return array The query fields
	 */
	protected function parseFields()
	{
		if ($this->settings['autoCreate'] && $GLOBALS['TSFE']->beUserLogin)
		{
			$this->createTable();
		}
		
		$dbFields = $this->db->admin_get_fields($this->table);
		
		foreach ($dbFields as $field => $properties)
		{
			if ($field != $this->key && !isset($this->settings['fields.'][$field]))
			{
				$this->settings['fields.'][$field.'.'] = array('mapping' => $field);
			}
		}
		
		return parent::parseFields();
	}
	
	/**
	 * Looks if the specified table exists and if not create it with the key-
	 * field (uid). Then it syncs the DB-fields with the fields found in the form 
	 * with help of template parser
	 */
	protected function createTable()
	{
		$templateFields = Tx_Formhandler_TemplateParser::getInstance()->getFields();
		$fields = array();
		foreach ($templateFields as $name => $field)
		{
			if (isset($field['type']) && $field['type'] == 'submit')
			{
				continue;
			}
			$fields[] = $name;
		}
		$this->db->debugOutput = 1;
		
		$res = $this->db->sql_query("SHOW TABLES LIKE '".$this->table."'");
		if (!$this->db->sql_num_rows($res))
		{
			$this->db->sql_query("CREATE TABLE `".$this->table."` (
            `".$this->key."` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
            )");
			$dbFields = array($this->key);
		}else{
			$dbFields = array_keys($this->db->admin_get_fields($this->table));
		}
		$createFields = array_diff($fields, $dbFields);
		if (count($createFields))
		{
			$sql = 'ALTER TABLE '.$this->table.' ADD `';
			$sql .= implode('` '.$this->newFieldsSqlAttribs.', ADD `', $createFields);
			$sql .= '` '.$this->newFieldsSqlAttribs.'';
			$this->db->sql_query($sql);
		}
	}
}
?>
