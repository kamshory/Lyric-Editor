<?php
namespace Pico\Config;
use Pico\DynamicObject\DynamicObject;
class ConfigApp extends DynamicObject
{
    /**
     * Constructor
     *
     * @param mixed $data Initial data
     * @param boolean $readonly Readonly flag
     */
    public function __construct($data = null, $database= null, $readonly = false)
    {
        if($data != null)
        {
            parent::__construct($data, $database);
        }
        $this->readOnly($readonly);
    }
    
}