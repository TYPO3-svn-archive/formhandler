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
 * Class for parsing the template html
 * 
 * @see http://forge.typo3.org/issues/9446
 * @author	Christian Opitz <co@netzelf.de>
 * @package	Tx_Formhandler
 * @subpackage	Utils
 */
class Tx_Formhandler_TemplateParser
{
	/**
	 * @var string Template file contents
	 */
	protected $templateFile = '';
	
	/**
	 * @var array Fields found in the template
	 */
	protected $fields = array();
	
	protected $foundStrings = array();
	
	/**
	 * @var Tx_Formhandler_TemplateParser
	 */
	protected static $instance;
	
	/**
	 * Constructor: Set file and parse it
	 */
	protected function __construct()
	{
		$this->templateFile = Tx_Formhandler_Globals::$templateCode;
		$this->extractFields();
	}
	
	/**
	 * @return Tx_Formhandler_TemplateParser
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Get all fields from the form that have a name attribute
	 */
	protected function extractFields()
	{
    	$pattern = '/\<(input|select|textarea)([^\>]*)\>/i';
    
        preg_match_all($pattern, $this->templateFile, $matches);
        
        $fields = array();
        
        $invokePrefixOnly = strlen(Tx_Formhandler_Globals::$formValuesPrefix) > 0;
        $prefix = Tx_Formhandler_Globals::$formValuesPrefix;
        
    	foreach ($matches[2] as $i => $attributeString)
        {
            if ($invokePrefixOnly && !strpos($attributeString, $prefix.'['))
            {
            	continue;
            }
        	preg_match_all('/\s*([a-zA-Z\-]*?)\s*=\s*[\'"](.*?)[\'"]/', $attributeString, $matches2, PREG_SET_ORDER);
            
            $attributes = array();
            $attributes['tag'] = $matches[1][$i];
            foreach ($matches2 as $attribute)
            {
            	$attributes[$attribute[1]] = $attribute[2];
            }
            
            if (isset($attributes['name']))
            {
                if (strpos($attributes['name'], '['))
                {
                    mb_parse_str($attributes['name'].'='.$attributes['value'], $value);
                    $keys = array_keys($value);
                    $attributes['name'] = $keys[0];
                    $value = $value[$keys[0]];
                    if (isset($fields[$attributes['name']]) && is_array($fields[$attributes['name']]['value']))
                    {
                    	$value = array_merge_recursive($fields[$attributes['name']]['value'], $value);
                    }
                    $attributes['value'] = $value;
                }
                $fields[$attributes['name']] = $attributes;
            }
        }
        
        $this->fields = ($invokePrefixOnly) ? $fields[$prefix] : $fields;
	}
	
	public function populateValues($gp)
	{
		var_dump($this->templateFile);
		$xml = new SimpleXMLElement($this->templateFile);
		foreach ($gp as $field => $value)
		{
			//if ()
		}
	}
	
	/**
	 * Get extracted fields
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}
}

?>