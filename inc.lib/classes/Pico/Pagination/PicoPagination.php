<?php
namespace Pico\Pagination;

use Pico\Exception\NullPointerException;

class PicoPagination
{
    /**
     * Offset
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Limit
     *
     * @var integer
     */
    private $limit = 0;

    /**
     * Order by
     *
     * @var string
     */
    private $orderBy = "";

    /**
     * Order type
     *
     * @var string
     */
    private $orderType = "";
    public function __construct($limit = 20)
    {
        $this->limit = $limit;
        $this->offset = $this->parseOffset();
        $this->orderBy = @$_GET['orderby'];
        $this->orderType = @$_GET['ordertype'];
    }

    /**
     * Parse offset
     *
     * @return integer
     */
    private function parseOffset()
    {
        if(isset($_GET['offset']))
        {
            $offsetStr = preg_replace("/\D/", "", $_GET['offset']);
            if($offsetStr == "")
            {
                $offset = 0;
            }
            else
            {
                $offset = abs((int) $offsetStr);
            }
            return $offset;
        }
        return 0;
    }

    /**
     * Get offset
     *
     * @return  integer
     */ 
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get limit
     *
     * @return  integer
     */ 
    public function getLimit()
    {
        return $this->limit;
    }

    public function getOrder($map = null, $filter = null, $default = null)
    {
        $orderBy = $this->getOrderBy($filter, $default);
        $orderByFixed = $orderBy;
        // mapping if any
        if($map != null && is_array($map) && isset($map[$orderBy]))
        {
            $orderByFixed = $map[$orderBy];
        }
        if($orderByFixed == null)
        {
            throw new NullPointerException("ORDER BY can not be null");
        }
        return $orderByFixed." ".$this->getOrderType();
    }

    /**
     * Get order by
     *
     * @var array $filter
     * @var string $default
     * @return string
     */ 
    public function getOrderBy($filter = null, $default = null)
    {
        if($filter != null && is_array($filter))
        {
            $orderBy = $this->orderBy;
            if(!in_array($orderBy, $filter))
            {
                $orderBy = null;
            }
            if($orderBy == null)
            {
                $orderBy = $default;
            }
        }
        if($orderBy == null)
        {
            throw new NullPointerException("ORDER BY can not be null");
        }
        return $orderBy;
    }

    /**
     * Get order type
     *
     * @return  string
     */ 
    public function getOrderType()
    {
        $orderType = $this->orderType;
        if(strcasecmp($orderType, 'desc') == 0)
        {
            $orderType = 'desc';
        }
        else
        {
            $orderType = 'asc';
        }
        return $orderType;
    }
}