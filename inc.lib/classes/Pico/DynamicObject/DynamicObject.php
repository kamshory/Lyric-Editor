<?php

namespace Pico\DynamicObject;

use PDOException;
use Pico\Database\PicoDatabase;
use Pico\Database\PicoDatabasePersistent;
use Pico\Exception\NoDatabaseConnectionException;
use Pico\Exception\NoRecordFoundException;
use Pico\Util\PicoAnnotationParser;
use Pico\Util\PicoEnvironmentVariable;
use ReflectionClass;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class to create dynamic object. 
 * Dynamic object is and object created from any class so that user can add any property with any name and value, load data from INI file, Yaml file, JSON file and database. 
 * User can also create entity from a table of database, insert, select, update and delete record from database. 
 */
class DynamicObject extends stdClass // NOSONAR
{
    const NO_DATABASE_CONNECTION = "No database connection provided";
    const NO_RECORD_FOUND = "No record found";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_DEFAULT_VALUE = "default_value";
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    
    /**
     * Flag readonly
     *
     * @var boolean
     */
    private $readonly = false; // NOSONAR

    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    private $database; // NOSONAR
    /**
     * Class params
     *
     * @var array
     */
    private $classParams = array();

    /**
     * Null properties
     *
     * @var array
     */
    private $nullProperties = array();

    /**
     * Get null properties
     *
     * @return array
     */
    public function nullPropertiyList()
    {
        return $this->nullProperties;
    }

    /**
     * Constructor
     *
     * @param mixed $data
     * @param PicoDatabase $database
     */
    public function __construct($data = null, $database = null)
    {
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            $vals = $jsonAnnot->parseKeyValue($paramValue);
            $this->classParams[$paramName] = $vals;
        }
        if($data != null)
        {
            $this->loadData($data);
        }
        if($database != null)
        {
            $this->database = $database;
        }
    }
    
    /**
     * Load data to object
     * @param mixed $data
     */
    public function loadData($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $key2 = $this->camelize($key);
                $this->set($key2, $value, true);
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
            $data = $env->replaceSysEnvAll($data, true);
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
    public function loadYamlFile($yml_file, $systemEnv = false, $asObject = false)
    {
        $data = Yaml::parseFile($yml_file);
        if($systemEnv)
        {
            $env = new PicoEnvironmentVariable();
            $data = $env->replaceSysEnvAll($data, true);
        }
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode($data));
            $this->loadData($obj);
        }
        else
        {
            $this->loadData($data);
        }
    }

    /**
     * Load data from JSON file
     *
     * @param string $json_file
     * @param bool $systemEnv
     * @return void
     */
    public function loadJsonFile($json_file, $systemEnv = false, $asObject = false)
    {
        $data = json_decode(file_get_contents($json_file));
        if($systemEnv)
        {
            $env = new PicoEnvironmentVariable();
            $data = $env->replaceSysEnvAll($data, true);
        }
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode($data));
            $this->loadData($obj);
        }
        else
        {
            $this->loadData($data);
        }
    }

    /**
     * Set readonly. When object is set to readonly, setter will not change value of its properties but loadData still works fine
     *
     * @param bool $readonly
     * @return void
     */
    protected function readOnly($readonly)
    {
        $this->readonly = $readonly;
    }

    /**
     * Set database connection
     * @var $database
     */
    public function withDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * Remove property
     *
     * @param object $sourceData
     * @param array $propertyNames
     * @return mixed
     */
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
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $persist->save($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Select data from database
     *
     * @return void
     */
    public function select()
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $data = $persist->select();
            if($data == null)
            {
                throw new NoRecordFoundException(self::NO_RECORD_FOUND);
            }
            $this->loadData($data);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
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
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $persist->insert($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
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
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $persist->update($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Delete data from database
     *
     * @return void
     */
    public function delete()
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $persist->delete();
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
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

    /**
     * Convert camel case to snake case
     *
     * @param string $input
     * @param string $glue
     * @return string
     */
    protected function snakeize($input, $glue = '_') {
        return ltrim(
            preg_replace_callback('/[A-Z]/', function ($matches) use ($glue) {
                return $glue . strtolower($matches[0]);
            }, $input),
            $glue
        );
    } 

    /**
     * Modify null properties
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return void
     */
    private function modifyNullProperties($propertyName, $propertyValue)
    {
        if($propertyValue === null && !isset($this->nullProperties[$propertyName]))
        {
            $this->nullProperties[$propertyName] = true; 
        }
        if($propertyValue != null && isset($this->nullProperties[$propertyName]))
        {
            unset($this->nullProperties[$propertyName]); 
        }
    }

    /**
     * Set property value
     *
     * @param string $propertyName
     * @param mixed|null
     * @param bool $skipModifyNullProperties
     * @return self
     */
    public function set($propertyName, $propertyValue, $skipModifyNullProperties = false)
    {
        $var = lcfirst($propertyName);
        $var = $this->camelize($var);
        $this->{$var} = $propertyValue;
        if(!$skipModifyNullProperties && $propertyValue === null)
        {
            $this->modifyNullProperties($var, $propertyValue);
        }
        return $this;
    }
    
    /**
     * Copy value from other object
     *
     * @param self|mixed $source
     * @param array $filter
     * @param boolean $includeNull
     * @return void
     */
    public function copyValueFrom($source, $filter = null, $includeNull = false)
    {
        if($filter != null)
        {
            $tmp = array();
            foreach($filter as $val)
            {
                $tmp[] = trim($this->camelize($val));
            }
            $filter = $tmp;
        }
        $values = $source->value();
        foreach($values as $property=>$value)
        {
            if(
                ($filter == null || (is_array($filter) && !empty($filter) && in_array($property, $filter))) 
                && 
                ($includeNull || $value != null)
                )
            {
                $this->set($property, $value);
            }
        }
    }

    /**
     * Unset property value
     *
     * @param string $propertyName
     * @param bool $skipModifyNullProperties
     * @return self
     */
    private function removeValue($propertyName, $skipModifyNullProperties = false)
    {
        return $this->set($propertyName, null, $skipModifyNullProperties);
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
     * Get default value
     *
     * @param boolean $snakeCase
     * @return stdClass
     */
    public function defatultValue($snakeCase = false)
    {
        $persist = new PicoDatabasePersistent($this->database, $this);
        $tableInfo = $persist->getTableInfo();
        $defaultValue = new stdClass;
        if(isset($tableInfo->defaultValue))
        {
            foreach($tableInfo->defaultValue as $column)
            {
                if(isset($column[self::KEY_NAME]))
                {
                    $columnName = trim($column[self::KEY_NAME]);
                    if($snakeCase)
                    {
                        $col = $this->snakeize($columnName);
                    }
                    else
                    {
                        $col = $columnName;
                    }
                    $defaultValue->$col = $persist->fixData($column[self::KEY_VALUE], $column[self::KEY_PROPERTY_TYPE]);
                }
            }
        }
        return $defaultValue;
    }
    
    /**
     * Fix value
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function fixValue($value, $type) // NOSONAR
    {
        if(strtolower($value) === 'true')
        {
            return true;
        }
        else if(strtolower($value) === 'false')
        {
            return false;
        }
        else if(strtolower($value) === 'null')
        {
            return false;
        }
        else if(is_numeric($value) && strtolower($type) != 'string')
        {
            return $value + 0;
        }
        else 
        {
            return $value;
        }
    }

    /**
     * Get object value
     * @return stdClass
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
     * Get object value
     * @return stdClass
     */
    public function valueObject($snakeCase = false)
    {
        return $this->value($snakeCase);
    }

    /**
     * Get object value as associative array
     * @return array
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }
    
    /**
     * Get object value as associated array with upper case first
     *
     * @return array
     */
    public function valueArrayUpperCamel()
    {
        $obj = clone $this;
        $array = (array) $obj->value();
        $renameMap = array();
        $keys = array_keys($array);
        foreach($keys as $key)
        {
            $renameMap[$key] = ucfirst($key);
        }          
        $array = array_combine(array_map(function($el) use ($renameMap) {
            return $renameMap[$el];
        }, array_keys($array)), array_values($array));
        return $array;
    }
    
    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    private function isSnake()
    {
        return isset($this->classParams['JSON'])
            && isset($this->classParams['JSON']['property-naming-strategy'])
            && strcasecmp($this->classParams['JSON']['property-naming-strategy'], 'SNAKE_CASE') == 0
            ;
    }
    
    /**
     *  Check if JSON naming strategy is upper camel case or not
     *
     * @return boolean
     */
    private function isUpperCamel()
    {
        return isset($this->classParams['JSON'])
            && isset($this->classParams['JSON']['property-naming-strategy'])
            && strcasecmp($this->classParams['JSON']['property-naming-strategy'], 'UPPER_CAMEL_CASE') == 0
            ;
    }
    
    /**
     * Check if JSON naming strategy is camel case or not
     *
     * @return boolean
     */
    protected function isCamel()
    {
        return !$this->isSnake();
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
     * Find all
     *
     * @param string $orderType
     * @param bool $passive
     * @return array
     */
    private function _findAll($orderType = null, $passive = false)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $result = $persist->findAll($orderType);
            if($result != null && !empty($result))
            {
                return $this->toArrayObject($result, $passive);
            }
            else
            {
                throw new NoRecordFoundException(self::NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }
    
    /**
     * Find one record by primary key value
     * 
     * @param mixed $params
     * @return self
     */
    public function find($params)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $result = $persist->find($params);
            if($result != null && !empty($result))
            {
                $this->loadData($result);
                return $this;
            }
            else
            {
                throw new NoRecordFoundException(self::NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find by params
     *
     * @param string $method
     * @param mixed $params
     * @param string $orderType
     * @param bool $passive
     * @return array
     */
    private function findBy($method, $params, $orderType = null, $passive = false)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $result = $persist->findBy($method, $params, $orderType);
            if($result != null && !empty($result))
            {
                return $this->toArrayObject($result, $passive);
            }
            else
            {
                throw new NoRecordFoundException(self::NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }
    
    /**
     * Delete by params
     *
     * @param string $method
     * @param mixed $params
     * @return bool
     */
    private function deleteBy($method, $params)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            return $persist->deleteBy($method, $params);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find one by params
     *
     * @param string $method
     * @param mixed $params
     * @return object
     */
    private function findOneBy($method, $params, $orderType = null)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            $result = $persist->findOneBy($method, $params, $orderType);
            if($result != null && !empty($result))
            {
                $this->loadData($result);
                return $this;
            }
            else
            {
                throw new NoRecordFoundException(self::NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }
    
    /**
     * Exists by params
     *
     * @param string $method
     * @param mixed $params
     * @param string $orderType
     * @return array
     */
    private function existsBy($method, $params)
    {
        if($this->database != null && $this->database->isConnected())
        {
            $persist = new PicoDatabasePersistent($this->database, $this);
            return $persist->existsBy($method, $params);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::NO_DATABASE_CONNECTION);
        }
    }
    
    /**
     * Convert boolean to text
     *
     * @param string $propertyName
     * @param string[] $params
     * @return string
     */
    private function booleanToTextBy($propertyName, $params)
    {
        $value = $this->get($propertyName);
        if(!isset($value))
        {
            $boolVal = false;
        }
        else
        {
            $boolVal = $value === true || $value == 1 || $value = "1"; 
        }
        return $boolVal?$params[0]:$params[1];
    }

    /**
     * Convert to array object
     *
     * @param array $result
     * @param bool $passive
     * @return array
     */
    private function toArrayObject($result, $passive = false)
    {
        $instance = array();
        foreach($result as $value)
        {
            $className = get_class($this);
            $instance[] = new $className($value, $passive ? null : $this->database);
        }
        return $instance;
    }
    
    /**
     * Get number of property of the object
     *
     * @return integer
     */
    public function size()
    {
        $parentProps = $this->propertyList(true, true);
        $length = 0;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $length++;
            }
        }
        return $length;
    }

    /**
     * Magic method called when user call any undefined method
     * is &raquo; get property value as boolean
     * get &raquo; get property value
     * set &raquo; set property value
     * unset &raquo; unset property value
     * findOneBy &raquo; search data from database and return one record
     * findFirstBy &raquo; search data from database and return first record
     * findLastBy &raquo; search data from database and return last record
     * findBy &raquo; search data from database
     * findAscBy &raquo; search data from database order by primary keys ascending
     * findDescBy &raquo; search data from database order by primary keys descending
     * findAll &raquo; search data from database without filter
     * findAllAsc &raquo; search data from database without filter order by primary keys ascending
     * findAllDesc &raquo; search data from database without filter order by primary keys descending
     * listBy &raquo; search data from database. Similar to findBy but does not contain a connection to the database so objects cannot be saved directly to the database
     * listAscBy &raquo; search data from database order by primary keys ascending. Similar to findAscBy but does not contain a connection to the database so objects cannot be saved directly to the database
     * listDescBy &raquo; search data from database order by primary keys descending. Similar to findDescBy but does not contain a connection to the database so objects cannot be saved directly to the database
     * listAll &raquo; search data from database without filter. Similar to findAll but does not contain a connection to the database so objects cannot be saved directly to the database
     * listAllAsc &raquo; search data from database without filter order by primary keys ascending. Similar to findAllAsc but does not contain a connection to the database so objects cannot be saved directly to the database
     * listAllDesc &raquo; search data from database without filter order by primary keys descending. Similar to findAllDesc but does not contain a connection to the database so objects cannot be saved directly to the database
     * deleteBy &raquo; delete data from database without read it first
     * booleanToTextBy &raquo; convert boolean value to yes/no or true/false depend on parameters given. Example: $result = booleanToTextByActive("Yes", "No"); If $obj->active is true, $result will be "Yes" otherwise "No"
     * booleanToSelectedBy &raquo; Create selected="selected" for form
     * booleanToCheckedBy &raquo; Create checked="checked" for form
     * existsBy &raquo; check data from database
     *
     * @param string $method
     * @param mixed $params
     * @return mixed|null
     */    
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "is", 2) === 0) {
            $var = lcfirst(substr($method, 2));
            return isset($this->$var) ? $this->$var == 1 : false;
        } else if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        else if (strncasecmp($method, "set", 3) === 0 && !$this->readonly) {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0 && !$this->readonly) {
            $var = lcfirst(substr($method, 5));
            $this->removeValue($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "findOneBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            return $this->findOneBy($var, $params);
        }
        else if (strncasecmp($method, "findFirstBy", 11) === 0) {
            $var = lcfirst(substr($method, 11));
            return $this->findOneBy($var, $params, PicoDatabasePersistent::ORDER_ASC);
        }
        else if (strncasecmp($method, "findLastBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            return $this->findOneBy($var, $params, PicoDatabasePersistent::ORDER_DESC);
        }
        else if (strncasecmp($method, "findBy", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            return $this->findBy($var, $params);
        }
        else if (strncasecmp($method, "findAscBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            return $this->findBy($var, $params, PicoDatabasePersistent::ORDER_ASC);
        }
        else if (strncasecmp($method, "findDescBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            return $this->findBy($var, $params, PicoDatabasePersistent::ORDER_DESC);
        }
        else if ($method == "findAll") {
            return $this->_findAll();
        }
        else if ($method == "findAllAsc") {
            return $this->_findAll(PicoDatabasePersistent::ORDER_ASC);
        }
        else if ($method == "findAllDesc") {
            return $this->_findAll(PicoDatabasePersistent::ORDER_DESC);
        }     
        else if (strncasecmp($method, "listBy", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            return $this->findBy($var, $params, null, true);
        }
        else if (strncasecmp($method, "listAscBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            return $this->findBy($var, $params, PicoDatabasePersistent::ORDER_ASC, true);
        }
        else if (strncasecmp($method, "listDescBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            return $this->findBy($var, $params, PicoDatabasePersistent::ORDER_DESC, true);
        }
        else if ($method == "listAll") {
            return $this->_findAll(null, true);
        }
        else if ($method == "listAllAsc") {
            return $this->_findAll(PicoDatabasePersistent::ORDER_ASC, true);
        }
        else if ($method == "listAllDesc") {
            return $this->_findAll(PicoDatabasePersistent::ORDER_DESC, true);
        }
        else if (strncasecmp($method, "deleteBy", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return $this->deleteBy($var, $params, null, true);
        }
        else if (strncasecmp($method, "booleanToTextBy", 15) === 0) {
            $prop = lcfirst(substr($method, 15));
            return $this->booleanToTextBy($prop, $params);
        }
        else if (strncasecmp($method, "booleanToSelectedBy", 19) === 0) {
            $prop = lcfirst(substr($method, 19));
            return $this->booleanToTextBy($prop, array(' selected="selected"', ''));
        }
        else if (strncasecmp($method, "booleanToCheckedBy", 18) === 0) {
            $prop = lcfirst(substr($method, 18));
            return $this->booleanToTextBy($prop, array(' cheked="checked"', ''));
        }
        else if (strncasecmp($method, "existsBy", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return $this->existsBy($var, $params);
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->$var) ? $this->$var : null;
            return isset($params[0]) && $params[0] == $value;
        }
    }

    /**
     * Magic method to stringify object
     *
     * @return string
     */
    public function __toString()
    {
        $obj = clone $this;
        $snake = $this->isSnake();
        $upperCamel = $this->isUpperCamel();
        if($upperCamel)
        {         
            $value = $this->valueArrayUpperCamel();
            return json_encode($value);
        }
        else 
        {
            return json_encode($obj->value($snake));
        }
    }
}
