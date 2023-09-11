<?php

namespace Pico\Data\Tools;

use Exception;
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

    private $rows = array();

    private function findAll()
    {
        try
        {           
            $result = $this->object->findByActive(true);
            foreach($result as $row)
            {
                $value = $row->get($this->map['value']);
                $label = $row->get($this->map['label']);
                $this->rows[] = array(
                    'value'=>$value,
                    'label'=>$label,
                    'selected'=>$value == $this->value
                );
            }
        }
        catch(Exception $e)
        {

        }
    }

    public function __toString()
    {
        return json_encode($this->rows);
    }
}