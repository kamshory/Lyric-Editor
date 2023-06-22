<?php

namespace Pico\DynamicObject;

use Pico\Util\PicoAnnotationParser;
use ReflectionClass;
use stdClass;

class SetterGetter
{
    private $object = null;
    public function __construct($object)
    {
        $this->object = $object;
    }
    /**
     * Convert snake case to camel case
     *
     * @param string $input
     * @param string $separator
     * @return string
     */
    protected function camelize($input, $separator = '_')
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    protected function snakeize($input, $glue = '_') {
        return ltrim(
            preg_replace_callback('/[A-Z]/', function ($matches) use ($glue) {
                return $glue . strtolower($matches[0]);
            }, $input),
            $glue
        );
    }

    /**
     * Set property value
     *
     * @param string $propertyName
     * @param mixed|null
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        $var = lcfirst($propertyName);
        $var = $this->camelize($var);
        $this->$var = $propertyValue;
        return $this;
    }

    /**
     * Get property value
     *
     * @param string $propertyName
     * @return mixed|null
     */
    public function get($propertyName)
    {
        $var = lcfirst($propertyName);
        $var = $this->camelize($var);
        return isset($this->$var) ? $this->$var : null;   
    }

    /**
     * Get value
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $value->$key = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = $this->snakeize($key);
                $value2->$key2 = $val;
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Property list
     * @var bool $reflectSelf
     * @return array
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        $properties = array_filter(
            $class->getProperties(), 
            function($property) use($class) { 
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );

        if($asArrayProps)
        {
            $result = array();
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[] = $prop;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Magic method called when user call any undefined method
     *
     * @param string $method
     * @param string $params
     * @return mixed|null
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        if (strncasecmp($method, "set", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            return $this;
        }
    }

    private function isSnake()
    {
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $annot = $jsonAnnot->getParameter('JSON');
        if(!empty($annot))
        {
            $vals = $jsonAnnot->parseKeyValue($annot);
            if($vals != null && isset($vals['property-naming-strategy']))
            {
                return strcasecmp($vals['property-naming-strategy'], 'SNAKE_CASE') == 0;
            }
        }
        return false;
    }

    public function __toString()
    {
        $obj = clone $this;
        return json_encode($obj->value($this->isSnake()));
    }
}