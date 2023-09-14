<?php

namespace Pico\Data\Tools;

use Exception;
use Pico\Database\PicoSortable;
use Pico\DynamicObject\DynamicObject;

class SelectOption
{
    /**
     * Object
     *
     * @var DynamicObject
     */
    private $object;

    /**
     * Map
     *
     * @var array
     */
    private $map = array();

    /**
     * Value
     *
     * @var mixed
     */
    private $value;
    
    /**
     * Attributes
     *
     * @var array
     */
    private $attributes = array();
    
    /**
     * Rows
     *
     * @var array
     */
    private $rows = array();

    /**
     * Constructor
     *
     * @param DynamicObject $object
     * @param array $map
     * @param mixed $value
     * @param array|null $attributes
     */
    public function __construct($object, $map, $value, $attributes = null)
    {
        $this->object = $object;
        $this->map = $map;
        $this->value = $value;
        $this->findAllActive();
        if(isset($attributes) && is_array($attributes))
        {
            $this->attributes = $attributes;
        }
    }

    /**
     * Create attributes
     *
     * @param DynamicObject $row
     * @param string $attr
     * @param string $value
     * @return array
     */
    private function createAttributes($row, $attr, $value)
    {
        $optAttributes = array();
        if(is_array($this->attributes))
        {
            foreach($this->attributes as $k=>$v)
            {
                $val = $row->get($v);
                if($val != null)
                {
                    $optAttributes[$k] = $val;
                }
            }
        }
        if($value == $this->value)
        {
            $optAttributes['selected'] = 'selected';
        }
        $optAttributes[$attr] = $value;
        return $optAttributes;
    }

    /**
     * Find all data from database
     *
     * @return void
     */
    private function findAllActive()
    {
        try
        {  
            $sortable = new PicoSortable(array('name', PicoSortable::ORDER_TYPE_ASC));         
            $result = $this->object->findByActive(true, $sortable);
            foreach($result->getResult() as $row)
            {
                $value = $row->get($this->map['value']);
                $label = $row->get($this->map['label']);
                $optAttributes = $this->createAttributes($row, 'value', $value);
                $this->rows[] = array(
                    'attribute'=>$optAttributes,
                    'textNode'=>$label
                );
            }
        }
        catch(Exception $e)
        {
            // do nothing
        }
    }

    /**
     * Convert associated array to HTML attributes as string
     *
     * @param array $array
     * @return string
     */
    private function attributeToString($array)
    {
        if($array == null || empty($array))
        {
            return "";
        }
        $optAttributes = array();
        foreach($array as $key=>$value)
        {
            $optAttributes[] = $key."=\"".htmlspecialchars($value)."\"";
        }
        return rtrim(" ".implode(" ", $optAttributes));
    }

    public function __toString()
    {
        $texts = array();
        foreach($this->rows as $row)
        {
            $optAttributes = $this->attributeToString($row['attribute']);
            $texts[] = "<option".$optAttributes.">".$row['textNode']."</option>";
        }
        return implode("\r\n", $texts);
    }
}