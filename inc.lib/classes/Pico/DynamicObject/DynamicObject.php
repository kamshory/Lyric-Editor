<?php

namespace Pico\DynamicObject;

use PDOException;
use Pico\Database\PicoDatabase;
use Pico\Database\PicoDatabasePersistent;
use Pico\Exception\NoDatabaseConnectionException;
use Pico\Exception\NoRecordFoundException;
use Pico\Util\PicoEnvironmentVariable;
use ReflectionClass;
use stdClass;
use Symfony\Component\Yaml\Yaml;

class DynamicObject extends stdClass
{
    const NO_DATABASE_CONNECTION = "No database connection provided";
    /**
     * Flag readonly
     *
     * @var boolean
     */
    private $__readonly = false;

    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    private $__database;
    /**
     * Constructor
     *
     * @param mixed $data
     * @param PicoDatabase $database
     */
    public function __construct($data = null, $database = null)
    {
        if($data != null)
        {
            $this->loadData($data);
        }
        if($database != null)
        {
            $this->__database = $database;
        }
    }
    /**
     * Load data to object
     * @param mixed $data
     */
    protected function loadData($data)
    {     
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $key2 = $this->camelize($key);
                $this->set($key2, $value);
            }
        }
    }

    /**
     * Load data from INI file
     *
     * @param string $ini_file
     * @param bool $systemEnv
     * @return void
     */
    public function loadIniFile($ini_file, $systemEnv = false)
    {
        // Parse without sections
        $data = parse_ini_file($ini_file);
        if($systemEnv)
        {
            $env = new PicoEnvironmentVariable();
            $data = $env->replaceSysEnvAll($data);
        }
        $this->loadData($data);
    }

    /**
     * Load data from Yaml file
     *
     * @param string $yml_file
     * @param bool $systemEnv
     * @return void
     */
    public function loadYamlFile($yml_file, $systemEnv = false)
    {     
        $data = Yaml::parseFile($yml_file);
        if($systemEnv)
        {
            $env = new PicoEnvironmentVariable();
            $data = $env->replaceSysEnvAll($data);
        }
        $this->loadData($data);
    }

    /**
     * Load data from JSON file
     *
     * @param string $json_file
     * @param bool $systemEnv
     * @return void
     */
    public function loadJsonFile($json_file, $systemEnv = false)
    {
        $data = json_decode(file_get_contents($json_file));
        if($systemEnv)
        {
            $env = new PicoEnvironmentVariable();
            $data = $env->replaceSysEnvAll($data);
        }
        $this->loadData($data);
    }

    /**
     * Set readonly. When object is set to readonly, setter will not change value of its properties but loadData still works fine
     *
     * @param bool $readonly
     * @return void
     */
    protected function readOnly($readonly)
    {
        $this->__readonly = $readonly;
    }

    /**
     * Set database connection
     */
    public function withDatabase($database)
    {
        $this->__database = $database;
        return $this;
    }
    

    public function removePropertyExcept($sourceData, $propertyNames)
    {
        if(is_object($sourceData))
        {
            // iterate
            $resultData = new stdClass;
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData->$key = $val;
                }
            }
            return $resultData;
        }
        if(is_array($sourceData))
        {
            // iterate
            $resultData = array();
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData[$key] = $val;
                }
            }
            return $resultData;
        }
        return new stdClass;
    }

    /**
     * Save to database
     * @param bool $includeNull
     *
     * @return void
     */
    public function save($includeNull = false)
    {
        if($this->__database != null)
        {
            $persist = new PicoDatabasePersistent($this->__database, $this);
            $persist->save($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Select data from database
     *
     * @return void
     */
    public function select()
    {
        if($this->__database != null)
        {
            $persist = new PicoDatabasePersistent($this->__database, $this);
            $data = $persist->select();
            $this->loadData($data);
        }
        else
        {
            throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Insert into database
     *
     * @param bool $includeNull
     * @return void
     * @throws NoDatabaseConnectionException|PDOException
     */
    public function insert($includeNull = false)
    {
        if($this->__database != null)
        {
            $persist = new PicoDatabasePersistent($this->__database, $this);
            $persist->insert($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Update data on database
     *
     * @param bool $includeNull
     * @return void
     */
    public function update($includeNull = false)
    {
        if($this->__database != null)
        {
            $persist = new PicoDatabasePersistent($this->__database, $this);
            $persist->update($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Delete data from database
     *
     * @return void
     */
    public function delete()
    {
        if($this->__database != null)
        {
            $persist = new PicoDatabasePersistent($this->__database, $this);
            $persist->delete();
        }
        else
        {
            throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
        }
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
        if (strncasecmp($method, "set", 3) === 0 && !$this->__readonly) {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            return $this;
        }
        if (strncasecmp($method, "findBy", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            if($this->__database != null)
            {
                $persist = new PicoDatabasePersistent($this->__database, $this);
                $result = $persist->findBy($var, $params[0]);
                if($result != null && !empty($result))
                {
                    return $this->toArrayObject($result);
                }
                else
                {
                    throw new NoRecordFoundException("No record found");
                }
            }
            else
            {
                throw new NoDatabaseConnectionException(DynamicObject::NO_DATABASE_CONNECTION);
            }         
        }
    }

    private function toArrayObject($result)
    {
        $instance = array();
        foreach($result as $value)
        {
            $className = get_class($this);
            $instance[] = new $className($value, $this->__database);
        }
        return $instance;
    }

    /**
     * Magic method to stringify object
     *
     * @return string
     */
    public function __toString()
    {
        $obj = clone $this;
        return json_encode($obj->value());
    }
}
