<?php

namespace Pico\Data\Tools;

use Exception;
use Pico\Database\PicoSort;
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
     */
    public function __construct($object, $map, $value)
    {
        $this->object = $object;
        $this->map = $map;
        $this->value = $value;
        $this->findAll();
    }

    

    private function findAll()
    {
        try
        {  
            $sortable = new PicoSortable(array('name', PicoSortable::ORDER_TYPE_ASC));         
            $result = $this->object->findByActive(true, $sortable);
            foreach($result->getResult() as $row)
            {
                $value = $row->get($this->map['value']);
                $label = $row->get($this->map['label']);
                $attributes = array('value'=>$value);
                if($value == $this->value)
                {
                    $attributes['selected'] = 'selected';
                }
                $this->rows[] = array(
                    'attribute'=>$attributes,
                    'textNode'=>$label
                );
            }
        }
        catch(Exception $e)
        {
            // do nothing
        }
    }

    private function arrayToAttribute($array)
    {
        $attributes = array();
        foreach($array as $key=>$value)
        {
            $attributes[] = $key."=\"".htmlspecialchars($value)."\"";
        }
        return rtrim(" ".implode(" ", $attributes));
    }

    public function __toString()
    {
        $texts = array();
        foreach($this->rows as $row)
        {
            $attributes = $this->arrayToAttribute($row['attribute']);
            $texts[] = "<option".$attributes.">".$row['textNode']."</option>";
        }
        return implode("\r\n", $texts);
    }
}