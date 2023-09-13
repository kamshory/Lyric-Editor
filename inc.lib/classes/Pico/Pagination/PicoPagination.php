<?php
namespace Pico\Pagination;

use Pico\Exceptions\NullPointerException;

class PicoPagination
{
    /**
     * Current page
     *
     * @var integer
     */
    private $currentPage = 0;

    /**
     * Page size
     *
     * @var integer
     */
    private $pageSize = 0;

    /**
     * Offset
     *
     * @var integer
     */
    private $offset = 0;
    
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



    public function __construct($pageSize = 20)
    {
        $this->pageSize = $pageSize;
        $this->currentPage = $this->parseCurrentPage();
        $this->offset = $this->pageSize * ($this->currentPage - 1);
        $this->orderBy = @$_GET['orderby'];
        $this->orderType = @$_GET['ordertype'];
    }

    /**
     * Parse offset
     *
     * @return integer
     */
    private function parseCurrentPage()
    {
        if(isset($_GET['page']))
        {
            $pageStr = preg_replace("/\D/", "", $_GET['page']);
            if($pageStr == "")
            {
                $page = 0;
            }
            else
            {
                $page = abs((int) $pageStr);
            }
            if($page < 1)
            {
                $page = 1;
            }
            return $page;
        }
        return 1;
    }

    /**
     * Create order
     *
     * @param array $map
     * @param array $filter
     * @param string $default
     * @return string
     */
    public function createOrder($map = null, $filter = null, $default = null)
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

   

    /**
     * Get current page
     *
     * @return  integer
     */ 
    public function getCurrentPage()
    {
        return $this->currentPage;
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

    /**
     * Get offset
     *
     * @return  integer
     */ 
    public function getOffset()
    {
        return $this->offset;
    }
}