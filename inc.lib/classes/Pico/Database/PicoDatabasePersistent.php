<?php

namespace Pico\Database;

use Exception;
use PDO;
use PDOStatement;
use Pico\Exception\EmptyResultException;
use Pico\Exception\NoColumnMatchException;
use Pico\Exception\NoInsertableColumnException;
use Pico\Exception\NoPrimaryKeyDefinedException;
use Pico\Exception\NoUpdatableColumnException;
use Pico\Util\PicoAnnotationParser;
use stdClass;

class PicoDatabasePersistent // NOSONAR
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_VAR = "var";
    const ANNOTATION_ID = "Id";
    const ANNOTATION_GENERATED_VALUE = "GeneratedValue";
    const ANNOTATION_NOT_NULL = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN = "DefaultColumn";
    
    const KEY_NAME = "name";
    const KEY_NULL = "null";
    const KEY_NOT_NULL = "notnull";
    const KEY_NULLABLE = "nullable";
    const KEY_INSERTABLE = "insertable";
    const KEY_UPDATABLE = "updatable";
    const KEY_STRATEGY = "strategy";
    const KEY_GENERATOR = "generator";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_VALUE = "value";
    
    const VALUE_TRUE = "true";
    const VALUE_FALSE = "false";

    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";
    
    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    private $database;

    /**
     * Object
     *
     * @var mixed
     */
    private $object;

    /**
     * Class name
     * @var string
     */
    private $className = "";

    /**
     * Skip null
     *
     * @var boolean
     */
    private $flagIncludeNull = false;

    /**
     * Database connection
     *
     * @param PicoDatabase $database
     * @param mixed $object
     */
    public function __construct($database, $object)
    {
        $this->database = $database;
        $this->className = get_class($object);
        $this->object = $object;
    }

    /**
     * Set flag to skip null column
     *
     * @param bool $skip
     * @return void
     */
    public function includeNull($skip)
    {
        $this->flagIncludeNull = $skip;
    }

    /**
     * Get table information
     *
     * @return stdClass
     */
    public function getTableInfo() // NOSONAR
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $reflexClass->parseKeyValue($table);
        $picoTableName = $values[self::KEY_NAME];
        $columns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();
        $defaultValue = array();

        // iterate each properties of the class
        foreach($props as $prop)
        {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, 'property');
            $parameters = $reflexProp->getParameters();

            // get column name of each parameters
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_COLUMN) == 0)
                {
                    $values = $reflexProp->parseKeyValue($val);
                    if(!empty($values))
                    {
                        $columns[$prop->name] = $values;
                    }
                }
            }

            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
                {
                    $type = explode(' ', trim($val, " \r\n\t "))[0];
                    $columns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                }
            }

            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
                {
                    $primaryKeys[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }

            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_GENERATED_VALUE) == 0 && isset($columns[$prop->name]))
                {
                    $vals = $reflexClass->parseKeyValue($val);
                    $autoIncrementKeys[$prop->name] = array(
                        self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                        self::KEY_STRATEGY=>isset($vals[self::KEY_STRATEGY])?$vals[self::KEY_STRATEGY]:null,
                        self::KEY_GENERATOR=>isset($vals[self::KEY_GENERATOR])?$vals[self::KEY_GENERATOR]:null
                    );
                }
            }
            
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_DEFAULT_COLUMN) == 0)
                {
                    $vals = $reflexClass->parseKeyValue($val);
                    if(isset($vals[self::KEY_VALUE]))
                    {
                        $defaultValue[$prop->name] = array(
                            self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                            self::KEY_VALUE=>$vals[self::KEY_VALUE],
                            self::KEY_PROPERTY_TYPE=>$columns[$prop->name][self::KEY_PROPERTY_TYPE]
                        );
                    }
                }
            }

            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_NOT_NULL) == 0 && isset($columns[$prop->name]))
                {
                    $notNullColumns[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }
        }
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $columns;
        $info->primaryKeys = $primaryKeys;
        $info->autoIncrementKeys = $autoIncrementKeys;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        return $info;
    }

    /**
     * Get match row
     *
     * @param PDOStatement $stmt
     * @return bool
     */
    public function matchRow($stmt)
    {
        if($stmt == null)
        {
            return false;
        }
        $rowCount = $stmt->rowCount();
        return $rowCount != null && $rowCount > 0;
    }
    
    /**
     * Save data to database
     *
     * @param bool $includeNull
     * @return PDOStatement
     */
    public function save($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        try
        {
            $where = $this->getWhere($info, $queryBuilder);
            $data2saved = clone $this->object->value();
            $data = $this->_select($info, $queryBuilder, $where);
            if($data != null)
            {
                // save current data
                foreach($data2saved as $prop=>$value)
                {
                    if($value != null)
                    {
                        $this->object->set($prop, $value);
                    }
                }
                $stmt = $this->_update($info, $queryBuilder, $where);
            }
            else
            {
                $stmt = $this->_insert($info, $queryBuilder);
            }
        }
        catch(Exception $e)
        {
            $stmt = $this->_insert($info, $queryBuilder);
        }     
        return $stmt;
    }

    /**
     * Get object values
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @return array
     */
    private function getValues($info, $queryBuilder)
    {
        $values = array();
        foreach($info->columns as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $this->object->get($property);
            if($this->flagIncludeNull || $value !== null)
            {
                $value = $queryBuilder->escapeValue($value);
                $values[$columnName] = $value;
            }
        }
        return $values;
    }

    /**
     * Get null column set manualy
     * @param stdClass $info
     * @return array
     */
    private function getNullCols($info)
    {
        $nullCols = array();
        $nullList = $this->object->nullPropertiyList();
        if(isset($nullList) && is_array($nullList))
        {
            foreach($nullList as $key=>$val)
            {
                if($val === true && isset($info->columns[$key]))
                {
                    $columnName = $info->columns[$key][self::KEY_NAME];
                    $nullCols[] = $columnName;
                }
            }
        }
        return $nullCols;
    }
    
    /**
     * Get noninsertable column
     * @param stdClass $info
     * @return array
     */
    private function getNonInsertableCols($info)
    {
        $nonInsertableCols = array();
        foreach($info->columns as $params)
        {
            if(isset($params[self::ANNOTATION_COLUMN]) 
            && isset($params[self::ANNOTATION_COLUMN][self::KEY_INSERTABLE])
            && strcasecmp($params[self::ANNOTATION_COLUMN][self::KEY_INSERTABLE], self::VALUE_FALSE) == 0                
                )
            {
                $columnName = $params[self::ANNOTATION_COLUMN][self::KEY_NAME];
                $nonInsertableCols[] = $columnName;
            }
        }
        return $nonInsertableCols;
    }
    
    /**
     * Get nonupdatable column
     * @param stdClass $info
     * @return array
     */
    private function getNonUpdatableCols($info)
    {
        $nonUpdatableCols = array();
        foreach($info->columns as $params)
        {
            if(isset($params[self::ANNOTATION_COLUMN]) 
            && isset($params[self::ANNOTATION_COLUMN][self::KEY_INSERTABLE])
            && strcasecmp($params[self::ANNOTATION_COLUMN][self::KEY_INSERTABLE], self::VALUE_FALSE) == 0              
                )
            {
                $columnName = $params[self::ANNOTATION_COLUMN][self::KEY_NAME];
                $nonUpdatableCols[] = $columnName;
            }
        }
        return $nonUpdatableCols;
    }

    /**
     * Get SET statement
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @return string
     */
    private function getSet($info, $queryBuilder)
    {
        $sets = array();
        $primaryKeys = $this->getPrimaryKeys($info);
        $nullCols = $this->getNullCols($info);
        $nonUpdatableCols = $this->getNonUpdatableCols($info);
        foreach($info->columns as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            if(!$this->isPrimaryKeys($columnName, $primaryKeys))
            {
                $value = $this->object->get($property);
                if(($this->flagIncludeNull || $value !== null) 
                    && !in_array($columnName, $nullCols) 
                    && !in_array($columnName, $nonUpdatableCols)
                    )
                {
                    $value = $queryBuilder->escapeValue($value);
                    $sets[] = $columnName . " = " . $value;
                }
            }
        }
        foreach($nullCols as $columnName)
        {
            $sets[] = "$columnName = null";
        }
        if(empty($sets))
        {
            throw new NoUpdatableColumnException("No updatable column");
        }
        return implode(", ", $sets);
    }

    /**
     * Get WHERE statement
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @return string
     */
    private function getWhere($info, $queryBuilder)
    {
        $wheres = array();
        foreach($info->primaryKeys as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $this->object->get($property);
            $value = $queryBuilder->escapeValue($value);
            if(strcasecmp($value, self::KEY_NULL) == 0)
            {
                $wheres[] = $columnName . " is null";
            }
            else
            {
                $wheres[] = $columnName . " = " . $value;
            }
        }
        if(empty($wheres))
        {
            throw new NoPrimaryKeyDefinedException("No primary key defined");
        }
        return implode(" and ", $wheres);
    }

    /**
     * Get primary keys
     *
     * @param stdClass $info
     * @return array
     */
    public function getPrimaryKeys($info)
    {
        $primaryKeys = array();
        foreach($info->primaryKeys as $column)
        {
            $primaryKeys[] = $column[self::KEY_NAME];
        }
        return $primaryKeys;
    }

    /**
     * Check if column is primary key or not
     *
     * @param string $columnName
     * @param array $primaryKeys
     * @return boolean
     */
    public function isPrimaryKeys($columnName, $primaryKeys)
    {
        return in_array($columnName, $primaryKeys);
    }

    /**
     * Get primary key with autoincrement value
     *
     * @param stdClass $info
     * @return array
     */
    public function getPrimaryKeyAutoIncrement($info)
    {
        $aiKeys = array();
        if(isset($info->autoIncrementKeys) && is_array($info->autoIncrementKeys))
        {
            $primaryKeys = array_keys($info->primaryKeys);
            foreach($info->autoIncrementKeys as $name=>$value)
            {
                if(in_array($name, $primaryKeys))
                {
                    $aiKeys[$name] = $value;
                }
            }
        }
        return $aiKeys;
    }

    /**
     * Insert data
     *
     * @param bool $includeNull
     * @return PDOStatement
     */
    public function insert($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        return $this->_insert($info, $queryBuilder);
    }

    /**
     * Insert data
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @return PDOStatement
     */
    private function _insert($info = null, $queryBuilder = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $values = $this->getValues($info, $queryBuilder);
        $fixValues = $this->fixInsertableValues($values, $info);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->insert()
            ->into($info->tableName)
            ->fields($this->createStatementFields($fixValues))
            ->values($this->createStatementValues($fixValues));
        $stmt = $this->database->executeQuery($sqlQuery);
        $keys = $this->getPrimaryKeyAutoIncrement($info);
        if(!empty($keys))
        {
            $props = array_keys($keys);
            $prop = $props[0];
            if($this->object->get($prop) == null)
            {
                $generatedValue = $this->database->getDatabaseConnection()->lastInsertId();
                $this->object->set($prop, $generatedValue);
            }
        }
        return $stmt;
    }
    
    /**
     * Fix insertable values
     *
     * @param array $values
     * @param stdClass $info
     * @return array
     */
    private function fixInsertableValues($values, $info = null)
    {
        $fixedValues = array();
        if($info != null)
        {
            $insertableCols = array();
            $nonInsertableCols = $this->getNonInsertableCols($info);
            foreach($values as $key=>$value)
            {
                if(!in_array($key, $nonInsertableCols))
                {
                    $insertableCols[$key] = $value;
                }
            }
            $fixedValues = $insertableCols;        
        }
        else
        {
            $fixedValues = $values;
        }       
        
        /**
         * 1. TABLE - Indicates that the persistence provider must assign primary keys for the entity using an underlying database table to ensure uniqueness.
         * 2. SEQUENCE - Indicates that the persistence provider must assign primary keys for the entity using a database sequence.
         * 3. IDENTITY - Indicates that the persistence provider must assign primary keys for the entity using a database identity column.
         * 4. AUTO - Indicates that the persistence provider should pick an appropriate strategy for the particular database. The AUTO generation strategy may expect a database resource to exist, or it may attempt to create one. A vendor may provide documentation on how to create such resources in the event that it does not support schema generation or cannot create the schema resource at runtime.
         * 5. UUID - Indicates that the persistence provider must assign primary keys for the entity with a UUID value.
         */ 
        
        if(isset($info->autoIncrementKeys))
        {
            foreach($info->autoIncrementKeys as $name=>$col)
            {
                if(strcasecmp($col[self::KEY_STRATEGY], "GenerationType.UUID") == 0)
                {
                    $value = $this->database->generateNewId();
                    $values[$col[self::KEY_NAME]] = $value;
                    $this->object->set($name, $value);
                }
            }
        }        
        if(empty($fixedValues))
        {
            throw new NoInsertableColumnException("No insertable column");
        }
        return $fixedValues;
    }

    /**
     * Implode array keys to field list
     *
     * @param array $values
     * @param stdClass $info
     * @return string
     */
    public function createStatementFields($values)
    {
        return "(".implode(", ", array_keys($values)).")";
    }

    /**
     * Implode array values to value list
     *
     * @param array $values
     * @return string
     */
    public function createStatementValues($values)
    {      
        return "(".implode(", ", array_values($values)).")";
    }

    /**
     * Get table column name from an object property
     *
     * @param string $propertyNames
     * @param array $columns
     * @return string
     */
    private function getColumnNames($propertyNames, $columns)
    {
        $result = $propertyNames;
        $arr = array();
        if(!empty($columns))
        {
            $keys = array_keys($columns);           
            foreach($keys as $key)
            {
                $pos = stripos($propertyNames, $key);
                if($pos !== false)
                {
                    $arr[$key] = $pos;
                }
            }
        } 
        asort($arr, SORT_REGULAR);
        $keys = array_keys($arr);
        foreach($keys as $pro)
        {
            if (isset($columns[$pro]))
            {
                $col = $columns[$pro];
                $columnName = $col[self::KEY_NAME];
                $result = str_ireplace($pro, " ".$columnName." ? ", $result);
            }
        }
        if($result != $propertyNames)
        {
            return $result;
        }
        throw new NoColumnMatchException("No column match");
    }

    /**
     * Fix comparison
     *
     * @param string $column
     * @return string
     */
    private function fixComparison($column)
    {
        if(stripos($column, ' ') !== false)
        {
            $arr = explode(' ', $column);
            foreach($arr as $idx=>$val)
            {
                if($val == 'And')
                {
                    $arr[$idx] = 'and';
                }
                if($val == 'Or')
                {
                    $arr[$idx] = 'or';
                }
            }
            $column = implode(' ', $arr);
        }
        return $column;
    }

    /**
     * Create WHERE by argument given
     *
     * @param object $info
     * @param string $propertyName
     * @param array $propertyValues
     * @return string
     */
    private function createWhereFromArgs($info, $propertyName, $propertyValues)
    {
        $columnNames = $this->getColumnNames($propertyName, $info->columns);
        $arr = explode("?", $columnNames);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();       
        $wheres = array();
        for($i = 0; $i < count($arr) - 1 && $i < count($propertyValues); $i++)
        {
            $column = ltrim($this->fixComparison($arr[$i]), ' ');
            if($propertyValues[$i] instanceof PicoDataComparation)
            {
                $wheres[] = $column . $propertyValues[$i]->getComparison() . " ". $queryBuilder->escapeValue($propertyValues[$i]->getValue());
            }
            else
            {
                $value = $queryBuilder->escapeValue($propertyValues[$i]);
                if(strcasecmp($value, 'null') == 0)
                {
                    $wheres[] = $column . "is " . $value;
                }
                else
                {
                    $wheres[] = $column . "= " . $value;
                }
            }
        }
        return implode(" ", $wheres);
    }

    /**
     * Create order by
     *
     * @param object $info
     * @param string $orderType
     * @return string
     */
    private function createOrderBy($info, $orderType)
    {
        $orderBys = array();
        $pKeys = array_values($info->primaryKeys);
        if(!empty($pKeys))
        {
            foreach($pKeys as $pKey)
            {
                $pKeyCol = $pKey[self::KEY_NAME];
                $orderBys[] = $pKeyCol." ".strtolower($orderType);
            }
        }
        return implode(", ", $orderBys);
    }
    
    /**
     * Find one record by primary key value
     *
     * @param array $propertyValues
     * @return array
     */
    public function find($propertyValues)
    {
        $data = null;
        $info = $this->getTableInfo();
        
        $primaryKeys = $info->primaryKeys;
        
        if(isset($primaryKeys) && !empty($primaryKeys) && count($primaryKeys) <= count($propertyValues))
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
            $wheres = array();
            $index = 0;
            foreach($primaryKeys as $primatyKey)
            {
                $columnName = $primatyKey[self::KEY_NAME];
                $columnValue = $propertyValues[$index];
                if($columnValue === null)
                {
                    $wheres[] = $columnName . " is null";
                }
                else
                {
                    $wheres[] = $columnName . " = " . $queryBuilder->escapeValue($propertyValues[$index]);
                }
            }
            $where = implode(" and ", $wheres);
            $sqlQuery = $queryBuilder
                ->newQuery()
                ->select($info->tableName.".*")
                ->from($info->tableName)
                ->where($where)
                ->limit(1)
                ->offset(0);
            try
            {
                $stmt = $this->database->executeQuery($sqlQuery);
                if($this->matchRow($stmt))
                {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $data = $this->fixDataType($row, $info); 
                }
                else
                {
                    $data = null;
                }
            }
            catch(Exception $e)
            {
                throw new EmptyResultException($e->getMessage());
            }
        }
        return $data;   
    }

    /**
     * Get all record from database
     *
     * @param string $orderType
     * @return array|null
     */
    public function findAll($orderType = null)
    {
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($info->tableName.".*")
            ->from($info->tableName);
        if($orderType != null)
        {
            $orderBy = $this->createOrderBy($info, $orderType);
            $sqlQuery->orderBy($orderBy);
        }
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = array();
                foreach($rows as $row)                
                {
                    $data = $this->fixDataType($row, $info); 
                }
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }

    /**
     * Get all mathced record from database
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @param string $orderType
     * @return array|null
     */
    public function findBy($propertyName, $propertyValue, $orderType = null)
    {
        $info = $this->getTableInfo();
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($info->tableName.".*")
            ->from($info->tableName)
            ->where($where);
        if($orderType != null)
        {
            $orderBy = $this->createOrderBy($info, $orderType);
            $sqlQuery->orderBy($orderBy);
        }
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = array();
                foreach($rows as $row)                
                {
                    $data = $this->fixDataType($row, $info); 
                }
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }
    
    /**
     * Check if data is exists or not
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return boolean
     */
    public function existsBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $primaryKeys = array_values($info->primaryKeys);
        $primaryKey = $primaryKeys[0][self::KEY_NAME];
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($primaryKey)
            ->from($info->tableName)
            ->where($where);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            return $this->matchRow($stmt) > 0;
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }
    
    /**
     * Delete data from database without read it first
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return boolean
     */
    public function deleteBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->delete()
            ->from($info->tableName)
            ->where($where);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            return $this->matchRow($stmt) > 0;
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }
    
    /**
     * Get all mathced record from database
     *
     * @param string $propertyName
     * @param array $propertyValues
     * @return array|null
     */
    public function findOneBy($propertyName, $propertyValue, $orderType = null)
    {
        $info = $this->getTableInfo();
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($info->tableName.".*")
            ->from($info->tableName)
            ->where($where);  
        if($orderType != null)
        {
            $orderBy = $this->createOrderBy($info, $orderType);
            $sqlQuery->orderBy($orderBy);
        }      
        $sqlQuery->limit(1)
            ->offset(0);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = $this->fixDataType($row, $info); 
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;   
    }

    /**
     * Fix data type
     *
     * @param array $data
     * @param stdClass $info
     * @return array
     */
    private function fixDataType($data, $info)
    {
        $result = array();
        $typeMap = $this->createTypeMap($info);
        foreach($data as $columnName=>$value)
        {
            if(isset($typeMap[$columnName]))
            {
                $result[$columnName] = $this->fixData($value, $typeMap[$columnName]);
            }
        }
        return $result;
    }

    /**
     * Fix value
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public function fixData($value, $type)
    {
        $type = strtolower($type);
        /*
        "double"=>"double",
        "float"=>"double",
        "bigint"=>"integer",
        "smallint"=>"integer",
        "tinyint(1)"=>"bool",
        "tinyint"=>"integer",
        "int"=>"integer",
        "varchar"=>"string",
        "char"=>"string",
        "tinytext"=>"string",
        "mediumtext"=>"string",
        "longtext"=>"string",
        "text"=>"string",   
        "enum"=>"string",   
        "boolean"=>"bool",
        "bool"=>"bool",
        "timestamp"=>"string",
        "datetime"=>"string",
        "date"=>"string",
        "time"=>"string"
        */
        $ret = $value;
        if($type == 'bool')
        {
            $ret = $value == 1 || $value == '1';
        }
        else if($type == 'integer')
        {
            if($value === null)
            {
                $ret = null;
            }
            else
            {
                $ret = intval($value);
            }
        }
        else if($type == 'double')
        {
            if($value === null)
            {
                $ret = null;
            }
            else
            {
                $ret = doubleval($value);
            }
        }
        else if($this->isDateTimeNull($value))
        {
            $ret = null;
        }
        return $ret;
    }
    
    private function isDateTimeNull($value)
    {
        if(!isset($value) || !is_string($value))
        {
            return false;
        }
        $value = str_replace("T", " ", $value);
        if(strlen($value) > 26)
        {
            $value = substr($value, 0, 26);
        }
        return $value == '0000-00-00 00:00:00.000000' 
            || $value == '0000-00-00 00:00:00.000' 
            || $value == '0000-00-00 00:00:00'
            || $value == '0000-00-00'
            ;
    }

    /**
     * Create type map
     *
     * @param stdClass $info
     * @return array
     */
    private function createTypeMap($info)
    {
        $map = array();
        if(isset($info) && isset($info->columns))
        {
            foreach($info->columns as $cols)
            {
                $map[$cols[self::KEY_NAME]] = $cols[self::KEY_PROPERTY_TYPE];
            }
        }
        return $map;
    }

    /**
     * Select record from database
     *
     * @return mixed
     */
    public function select()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_select($info, $queryBuilder, $where);
    }

    /**
     * Select record from database with primary keys given
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @param string $where
     * @return mixed
     */
    private function _select($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($info->tableName.".*")
            ->from($info->tableName)
            ->where($where);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = $this->fixDataType($row, $info); 
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }

    /**
     * Update
     *
     * @param boolean $includeNull
     * @return PDOStatement
     */
    public function update($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_update($info, $queryBuilder, $where);
    }

    /**
     * Update record on database with primary keys given
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @param string $where
     * @return PDOStatement
     */
    private function _update($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        $set = $this->getSet($info, $queryBuilder);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->update($info->tableName)
            ->set($set)
            ->where($where);
        return $this->database->executeQuery($sqlQuery);
    }

    /**
     * Delete record from database
     *
     * @return PDOStatement
     */
    public function delete()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_delete($info, $queryBuilder, $where);
    }

    /**
     * Delete record from database with primary keys given
     *
     * @param stdClass $info
     * @param PicoDatabaseQueryBuilder $queryBuilder
     * @param string $where
     * @return PDOStatement
     */
    private function _delete($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }              
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->delete()
            ->from($info->tableName)
            ->where($where);
        return $this->database->executeQuery($sqlQuery);
    }
}