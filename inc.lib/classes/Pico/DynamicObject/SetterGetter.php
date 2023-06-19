<?php

namespace Pico\DynamicObject;

use stdClass;

class SetterGetter
{
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

    public function value()
    {
        $value = new stdClass;
        foreach ($this as $key => $val) {
            $value->$key = $val;
        }
        return $value;
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

    public function __toString()
    {
        return json_encode($this->value());
    }
}