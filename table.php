<?php

use Pico\Data\Song;
use Pico\Util\PicoAnnotationParser;

require_once "inc/auth.php";

$reflexClass = new PicoAnnotationParser("Pico\Data\Song");

$table = $reflexClass->getParameter("Table");
$values = $reflexClass->parseKeyValue($table);
$tableName = $values['name'];      
$columns = array();
$primaryKeys = array();
$notNullColumns = array();
$props = $reflexClass->getProperties();
$className = "Pico\Data\Song";
foreach($props as $prop)
{       
    $reflexProp = new PicoAnnotationParser($className, $prop->name, 'property');
    $parameters = $reflexProp->getParameters();
    foreach($parameters as $param=>$val)
    {
        if(strtolower($param) == 'column')
        {
            $values = $reflexProp->parseKeyValue($val);                    
            if(count($values) > 0)
            {
                $columns[$prop->name] = $values;
            }
        }          
    }
    foreach($parameters as $param=>$val)
    {
        if(strtolower($param) == 'var' && isset($columns[$prop->name]))
        {
            $type = explode(' ', trim($val, " \r\n\t "))[0];
            $columns[$prop->name]['propertyType'] = $type;
                      
        }
    }
    foreach($parameters as $param=>$val)
    {
        if(strtolower($param) == 'id' && isset($columns[$prop->name]))
        {
            $primaryKeys[$prop->name] = array('name'=>$columns[$prop->name]['name']);       
            if(isset($columns[$prop->name]['propertyType']))   
            {
                $primaryKeys[$prop->name]['propertyType'] = $columns[$prop->name]['propertyType'];
            }     
        }
    }
    foreach($parameters as $param=>$val)
    {
        if(strtolower($param) == 'notnull' && isset($columns[$prop->name]))
        {
            $notNullColumns[$prop->name] = array('name'=>$columns[$prop->name]['name']);     
            if(isset($columns[$prop->name]['propertyType']))   
            {
                $notNullColumns[$prop->name]['propertyType'] = $columns[$prop->name]['propertyType'];
            }    
        }               
    }
}
$info = new stdClass;
$info->tableName = $tableName;
$info->columns = $columns;
$info->primaryKeys = $primaryKeys;
$info->notNullColumns = $notNullColumns;

print_r($info);