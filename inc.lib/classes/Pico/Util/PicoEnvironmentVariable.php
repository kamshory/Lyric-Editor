<?php

namespace Pico\Util;

class PicoEnvironmentVariable
{
    /**
     * Replace all values with environment variable
     *
     * @param array $values
     * @return array
     */
    public function replaceSysEnvAll($values, $recursive = false)
    {
        foreach($values as $key=>$value)
        {
            if($recursive)
            {
                if(is_object($value) || is_array($value))
                {
                    $value = $this->replaceSysEnvAll($value, $recursive);
                }
                else
                {
                    $value = $this->replaceSysEnv($value);
                }
            }
            else
            {
                $value = $this->replaceSysEnv($value);
            }
            $values[$key] = $value;
        }
        return $values;
    }

    /**
     * Replace value with environment variable
     *
     * @param string $value
     * @return string
     */
    public function replaceSysEnv($value)
    {
        $vars = $this->getVariables($value);
        foreach($vars as $key)
        {
            $systemEnv = getenv($key);
            $key2 = '${'.$key.'}';
            if($systemEnv !== false)
            {
                $value = str_replace($key2, $systemEnv, $value);
            }
        }
        return $value;
    }

    /**
     * Get environment variable nane from a string
     *
     * @param string $value
     * @return array
     */
    public function getVariables($value)
    {
        $result = array();
        $arr = explode('${', $value);
        if(count($arr) > 1)
        {
            $cnt = count($arr);
            for($i = 1; $i < $cnt; $i++)
            {
                if(stripos($arr[$i], "}") !== false)
                {
                    $arr2 = explode('}', $arr[$i]);
                    $result[] = trim($arr2[0]);
                }
            }
        }
        return $result;
    }
}