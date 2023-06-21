<?php
namespace Pico\Database;

use Exception;
use PDO;
use PDOStatement;
use Pico\Exception\EmptyResultException;
use Pico\Exception\NoColumnMatchException;
use Pico\Exception\NoColumnUpdatedException;
use Pico\Exception\NoPrimaryKeyDefinedException;
use Pico\Exception\NotNullColumnException;
use Pico\Util\PicoAnnotationParser;
use stdClass;

class PicoDatabasePersistent
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_VAR = "var";
    const ANNOTATION_ID = "Id";
    const ANNOTATION_NOT_NULL = "NotNull";
    const KEY_NAME = "name";
    const KEY_NULL = "null";
    const KEY_NOT_NULL = "notnull";
    
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
        $tableName = $values[self::KEY_NAME];      
        $columns = array();
        $primaryKeys = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();

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
                    if(count($values) > 0)
                    {
                        $columns[$prop->name] = $values;
                    }
                }          
            }

            // get property type of each parameters which have column
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
                {
                    $type = explode(' ', trim($val, " \r\n\t "))[0];
                    $columns[$prop->name]['propertyType'] = $type;                          
                }
            }

            // get property type of each parameters which is primary key 
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
                {
                    $primaryKeys[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);            
                }
            }

            // get property type of each parameters which is not null
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_NOT_NULL) == 0 && isset($columns[$prop->name]))
                {
                    $notNullColumns[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);            
                }               
            }
        }
        $info = new stdClass;
        $info->tableName = $tableName;
        $info->columns = $columns;
        $info->primaryKeys = $primaryKeys;
        $info->notNullColumns = $notNullColumns;
        return $info;
    }

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
     * Save data
     *
     * @param bool $includeNull
     * @return PDOStatement
     */
    public function save($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        $data = $this->_select($info, $queryBuilder, $where);
        if($data != null)
        {
            $stmt = $this->_update($info, $queryBuilder, $where);
        }
        else
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
            if($this->flagIncludeNull || $value != null)
            {
                $value = $queryBuilder->escapeValue($value);
                $values[$columnName] =  $value;
            }
        }
        return $values;
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
        foreach($info->columns as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            if(!$this->isPrimaryKeys($columnName, $primaryKeys))
            {
                $value = $this->object->get($property);
                if($this->flagIncludeNull || $value != null)
                {
                    $value = $queryBuilder->escapeValue($value);
                    $sets[] = "$columnName = $value";
                }
            }
        }
        if(empty($sets))
        {
            throw new NoColumnUpdatedException("No column updated");
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
                if(isset($column[self::KEY_NULL]) && strcasecmp($column[self::KEY_NULL], self::KEY_NOT_NULL) == 0 || in_array($property, array_keys($info->notNullColumns)))
                {
                    throw new NotNullColumnException(sprintf("Property {%s} / column {%s} can not be null", $property, $columnName));
                }
                $wheres[] = "$columnName is null";
            }
            else
            {
                $wheres[] = "$columnName = $value";
            }
        }
        if(empty($wheres))
        {
            throw new NoPrimaryKeyDefinedException("No primary key defined");
        }    
        return implode(" and ", $wheres);
    }

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
     * Insert data
     *
     * @param bool $includeNull
     * @return PDOStatement
     */
    public function insert($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        return $this->_insert();
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
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->insert()
            ->into($info->tableName)
            ->fields($this->createStatementFields($values))
            ->values($this->createStatementValues($values));
        return $this->database->executeQuery($sqlQuery);
    }

    /**
     * Implode array keys to field list
     *
     * @param array $values
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
     * @param string $propertyName
     * @param array $columns
     * @return string
     */
    public function getColumnName($propertyName, $columns)
    {
        if(!empty($columns))
        {      
            foreach($columns as $pro=>$col)
            {
                if (strcasecmp($pro, $propertyName) == 0)
                {
                    return $col[self::KEY_NAME];
                }
            }
        }
        throw new NoColumnMatchException("No column match");
    }

    /**
     * Get all mathced record from database
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return array|null
     */
    public function findBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $columnName = $this->getColumnName($propertyName, $info->columns);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();        
        $where = "$columnName = ".$queryBuilder->escapeValue($propertyValue);
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
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->object->loadData($data);
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