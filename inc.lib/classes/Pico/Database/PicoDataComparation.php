<?php

namespace Pico\Database;

class PicoDataComparation
{
    const EQUALS = "=";
    const NOT_EQUALS = "!=";
    const LESS_THAN = "<";
    const GREATER_THAN = ">";
    const LESS_THAN_OR_EQUALS = "<=";
    const GREATER_THAN_OR_EQUALS = ">=";
    const TYPE_STRING = "string";
    const TYPE_BOOLEAN = "boolean";
    const TYPE_NUMERIC = "numeric";
    const TYPE_NULL = "null";
    
    private $comparison = "=";
    private $value = null;
    private $type = "null";

    /**
     * Equals
     * @param mixed $value
     */
    public static function equals($value)
    {
        return new PicoDataComparation($value, self::EQUALS);
    }

    /**
     * Not equals
     * @param mixed $value
     */
    public static function notEquals($value)
    {
        return new PicoDataComparation($value, self::NOT_EQUALS);
    }

    /**
     * Less than
     * @param mixed $value
     */
    public static function lessThan($value)
    {
        return new PicoDataComparation($value, self::LESS_THAN);
    }

    /**
     * Greater than
     * @param mixed $value
     */
    public static function greaterThan($value)
    {
        return new PicoDataComparation($value, self::GREATER_THAN);
    }

    /**
     * Less than or equals
     * @param mixed $value
     */
    public static function lessThanOrEquals($value)
    {
        return new PicoDataComparation($value, self::LESS_THAN_OR_EQUALS);
    }

    /**
     * Greater than or equals
     * @param mixed $value
     */
    public static function greaterThanOrEquals($value)
    {
        return new PicoDataComparation($value, self::GREATER_THAN_OR_EQUALS);
    }

    /**
     * Constructor
     * 
     * @param mixed $value
     * @param string $comparison
     */
    public function __construct($value, $comparison=self::EQUALS)
    {
        $this->comparison = $comparison;
        $this->value = $value;
        if(is_string($value))
		{
			$this->type = self::TYPE_STRING;
		}
		else if(is_bool($value))
		{
			$this->type = self::TYPE_BOOLEAN;
		}
		else if(is_numeric($value))
		{
            $this->type = self::TYPE_NUMERIC;
        }
    }

    /**
     * Get equals operator
     *
     * @return string
     */
    private function _equals()
    {
        if($this->value === null || $this->type == self::TYPE_NULL)
        {
            return "is";
        }
        else
        {
            return "=";
        }
    }

    /**
     * Get not equals operator
     *
     * @return string
     */
    private function _notEquals()
    {
        if($this->value === null || $this->type == self::TYPE_NULL)
        {
            return "is not";
        }
        else
        {
            return "!=";
        }
    }

    /**
     * Get less than operator
     *
     * @return string
     */
    private function _lessThan()
    {
        return "<";
    }
    
    /**
     * Get greater than operator
     *
     * @return string
     */
    private function _greaterThan()
    {
        return ">";
    }

    /**
     * Get less than or equals operator
     *
     * @return string
     */
    private function _lessThanOrEquals()
    {
        return "<=";
    }
    
    /**
     * Get greater than or equals operator
     *
     * @return string
     */
    private function _greaterThanOrEquals()
    {
        return ">=";
    }

    /**
     * Get comparison operator
     *
     * @return string
     */
    public function getComparison() // NOSONAR
    {
        if($this->comparison === self::NOT_EQUALS)
        {
            return $this->_notEquals();
        }
        if($this->comparison === self::LESS_THAN)
        {
            return $this->_lessThan();
        }
        if($this->comparison === self::GREATER_THAN)
        {
            return $this->_greaterThan();
        }
        if($this->comparison === self::LESS_THAN_OR_EQUALS)
        {
            return $this->_lessThanOrEquals();
        }
        if($this->comparison === self::GREATER_THAN_OR_EQUALS)
        {
            return $this->_greaterThanOrEquals();
        }
        else
        {
            return $this->_equals();
        }
    }

    /**
     * Get the value of property value
     */ 
    public function getValue()
    {
        return $this->value;
    }
}