<?php

namespace Pico\Database;

use Pico\DynamicObject\DynamicObject;
use stdClass;

class PicoPageData
{
    const RESULT = 'result';
    const PAGABLE = 'pagable';

    /**
     * Result
     *
     * @var DynamicObject[]
     */
    private $result = array();

    /**
     * Pagable
     *
     * @var PicoPagable
     */
    private $pagable;

    /**
     * Total match
     *
     * @var integer
     */
    private $totalResult = 0;

    /**
     * Total page
     *
     * @var integer
     */
    private $totalPage = 0;
    /**
     * Page number
     * @var integer
     */
    private $pageNumber = 0;

    /**
     * Page size
     * @var integer
     */
    private $pageSize = 0;
    
    /**
     * Start time
     *
     * @var float
     */
    private $startTime = 0.0;
    
    /**
     * End time
     *
     * @var float
     */
    private $endTime = 0.0;
    
    /**
     * Execution time
     *
     * @var float
     */
    private $executionTime = 0.0;

    /**
     * Constructor
     *
     * @param array $result
     * @param PicoPagable $pagable
     * @param integer $match
     */
    public function __construct($result, $pagable, $totalResult, $startTime)
    {
        $this->startTime = $startTime;
        $this->result = $result;
        $this->pagable = $pagable;
        $this->totalResult = $totalResult;
        if($pagable != null && $pagable instanceof PicoPagable)
        {
            $this->calculateContent();
        }
        $this->endTime = microtime(true);
        $this->executionTime = $this->endTime - $this->startTime;
    }

    private function calculateContent()
    {
        $this->pageNumber = $this->pagable->getPage()->getPageNumber();
        $this->totalPage = ceil($this->totalResult / $this->pagable->getPage()->getPageSize());
        $this->pageSize = $this->pagable->getPage()->getPageSize();
    }

    /**
     * Get result
     *
     * @return  DynamicObject[]
     */ 
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get page number
     *
     * @return  integer
     */ 
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Get total page
     *
     * @return  integer
     */ 
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * Get page size
     *
     * @return  integer
     */ 
    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function __toString()
    {
        $obj = new stdClass;
        foreach($this as $key=>$value)
        {
            if($key != self::RESULT && $key != self::PAGABLE)
            {
                $obj->{$key} = $value;
            }
        }
        return json_encode($obj);
    }

    /**
     * Get execution time
     *
     * @return  float
     */ 
    public function getExecutionTime()
    {
        return $this->executionTime;
    }
}