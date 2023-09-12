<?php

namespace Pico\Database;

/**
 * Limit and offset select database records
 */
class PicoLimit
{
    private $limit = 0;
    private $offset = 0;
    public function __construct($offset = 0, $limit = 0)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * Get the value of limit
     */ 
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
     * @return self
     */ 
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the value of offset
     */ 
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the value of offset
     *
     * @return self
     */ 
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }
}