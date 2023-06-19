<?php
namespace Pico\Pagination;
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

    /**
     * Get order by
     *
     * @return  string
     */ 
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Get order type
     *
     * @return  string
     */ 
    public function getOrderType()
    {
        return $this->orderType;
    }
}