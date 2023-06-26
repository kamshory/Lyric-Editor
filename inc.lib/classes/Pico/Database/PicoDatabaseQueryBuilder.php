<?php
namespace Pico\Database;

class PicoDatabaseQueryBuilder // NOSONAR
{
	/**
	 * Buffer
	 *
	 * @var string
	 */
	private $buffer = "";

	/**
	 * Has limit and offset
	 *
	 * @var boolean
	 */
	private $limitOffset = false;

	/**
	 * Limit
	 *
	 * @var integer
	 */
	private $limit = 0;

	/**
	 * Offset
	 *
	 * @var integer
	 */
	private $offset = 0;

	/**
	 * Database type
	 *
	 * @var string
	 */
	private $databaseType = "mysql";

	/**
	 * Database
	 *
	 * @param mixed $databaseType
	 */
	public function __construct($databaseType)
	{
		if($databaseType instanceof PicoDatabase)
		{
			$databaseType->getDatabaseType();
		}
		else
		{
			$this->databaseType = $databaseType;
		}	
	}

	/**
	 * Check if database type is MySQL or MariaDB
	 *
	 * @return boolean
	 */
	public function isMySql()
	{
		return strcasecmp($this->databaseType, "mysql") == 0 || strcasecmp($this->databaseType, "mariadb") == 0;
	}

	/**
	 * Check if database type is PostgreSQL
	 *
	 * @return boolean
	 */
	public function isPgSql()
	{
		return strcasecmp($this->databaseType, "postgresql") == 0;
	}

	/**
	 * Empty buffer, limit and offset
	 *
	 * @return self
	 */
	public function newQuery()
	{
		$this->buffer = "";
		$this->limitOffset = false;
		return $this;
	}

	/**
	 * Create insert statement
	 *
	 * @return self
	 */
	public function insert()
	{
		$this->buffer = "insert \r\n";
		return $this;
	}

	/**
	 * Create into statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function into($query)
	{
		$this->buffer .= "into $query\r\n";
		return $this;
	}

	/**
	 * Create select statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function select($query = "")
	{
		$this->buffer .= "select $query\r\n";
		return $this;
	}

	/**
	 * Create alias statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function alias($query)
	{
		$this->buffer .= "as $query\r\n";
		return $this;
	}

	/**
	 * Create field statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function fields($query)
	{
		$this->buffer .= "$query \r\n";
		return $this;
	}

	/**
	 * Create values statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function values($query)
	{	
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "values $buffer \r\n";
		}
		else
		{
			$this->buffer .= "values $query \r\n";
		}
		return $this;
	}

	/**
	 * Create delete statement
	 *
	 * @return self
	 */
	public function delete()
	{
		$this->buffer .= "delete \r\n";
		return $this;
	}

	/**
	 * Create from statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function from($query)
	{
		$this->buffer .= "from $query \r\n";
		return $this;
	}

	/**
	 * Create join statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function join($query)
	{
		$this->buffer .= "join $query \r\n";
		return $this;
	}

	/**
	 * Create inner join statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function innerJoin($query)
	{
		$this->buffer .= "inner join $query \r\n";
		return $this;
	}

	/**
	 * Create outer join statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function outerJoin($query)
	{
		$this->buffer .= "outer join $query \r\n";
		return $this;
	}

	/**
	 * Create left join statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function leftJoin($query)
	{
		$this->buffer .= "left join $query \r\n";
		return $this;
	}

	/**
	 * Create right join statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function rightJoin($query)
	{
		$this->buffer .= "right join $query \r\n";
		return $this;
	}

	/**
	 * Create on statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function on($query)
	{
		$this->buffer .= "on $query \r\n";
		return $this;
	}

	/**
	 * Create update statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function update($query)
	{
		$this->buffer .= "update $query \r\n";
		return $this;
	}

	/**
	 * Create set statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function set($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "set $buffer \r\n";
		}
		else
		{
			$this->buffer .= "set $query \r\n";
		}	
		return $this;
	}

	/**
	 * Create where statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function where($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "where $buffer \r\n";
		}
		else
		{
			$this->buffer .= "where $query \r\n";
		}		
		return $this;
	}

	/**
	 * Create match value
	 *
	 * @param array $args
	 * @return string
	 */
	public function createMatchedValue($args)
	{
		$result = "";
		if(count($args) > 1)
		{
			$format = $args[0];
			$formats = explode('?', $format);
			$len = count($args) - 1;
			$values = array();
			for($i = 0; $i<$len; $i++)
			{
				$j = $i + 1;
				$values[$i] = $this->escapeValue($args[$j]);
			}
			for($i = 0; $i<$len; $i++)
			{
				$result .= $formats[$i];
				if($j <= $len)
				{
					$result .= $values[$i];
				}			
			}
		}
		return $result;
	}

	/**
	 * Escape value
	 * @var mixed
	 * @return string
	 */
	public function escapeValue($value)
	{
		if($value === null)
		{
			// null
			return 'null';
		}
		else if(is_string($value))
		{
			// escape the value
			return "'".$this->escapeSQL($value)."'";
		}
		else if(is_bool($value))
		{
			// true or false
			return $value?'true':'false';
		}
		else if(is_numeric($value))
		{
			// convert number to string
			return $value."";
		}
		else if(is_array($value) || is_object($value))
		{
			// encode to JSON and escapethe value
			return "'".$this->escapeSQL(json_encode($value))."'";
		}
		else
		{
			// force convert to string and escapethe value
			return "'".$this->escapeSQL($value)."'";
		}
	}

	/**
	 * Create having statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function having($query)
	{
		$this->buffer .= "having $query \r\n";
		return $this;
	}

	/**
	 * Create order by statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function orderBy($query)
	{
		$this->buffer .= "order by $query \r\n";
		return $this;
	}

	/**
	 * Create goup by statement
	 *
	 * @param string $query
	 * @return self
	 */
	public function groupBy($query)
	{
		$this->buffer .= "group by $query \r\n";
		return $this;
	}

	/**
	 * Set limit
	 *
	 * @param [type] $limit
	 * @return self
	 */
	public function limit($limit)
	{
		$this->limitOffset = true;
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set offset
	 *
	 * @param [type] $offset
	 * @return self
	 */
	public function offset($offset)
	{
		$this->limitOffset = true;
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Create lock tables statement
	 *
	 * @param string $tables
	 * @return string|null
	 */
	public function lockTables($tables)
	{
		if($this->isMySql())
		{
			return "lock tables $tables";
		}
		if($this->isPgSql())
		{
			return "lock tables $tables";
		}
		return null;
	}

	/**
	 * Create unlock tables statement
	 *
	 * @return string|null
	 */
	public function unlockTables()
	{
		if($this->isMySql())
		{
			return "unlock tables";
		}
		if($this->isPgSql())
		{
			return "unlock tables";
		}
		return null;
	}

	/**
	 * Create start transaction statement
	 *
	 * @return string|null
	 */
	public function startTransaction()
	{
		if($this->isMySql())
		{
			return "start transaction";
		}
		if($this->isPgSql())
		{
			return "start transaction";
		}
		return null;
	}

	/**
	 * Create commit statement
	 *
	 * @return string|null
	 */
	public function commit()
	{
		if($this->isMySql())
		{
			return "commit";
		}
		if($this->isPgSql())
		{
			return "commit";
		}
		return null;
	}

	/**
	 * Create rollback statement
	 *
	 * @return string|null
	 */
	public function rollback()
	{
		if($this->isMySql())
		{
			return "rollback";
		}
		if($this->isPgSql())
		{
			return "rollback";
		}
		return null;
	}

	/**
	 * Create execute function statement
	 *
	 * @param string $name
	 * @param string $params
	 * @return string|null
	 */
	public function executeFunction($name, $params)
	{
		if($this->isMySql())
		{
			return "select $name($params)";
		}
		if($this->isPgSql())
		{
			return "select $name($params)";
		}
		return null;
	}

	/**
	 * Create execute procedure statement
	 *
	 * @param string $name
	 * @param string $params
	 * @return string|null
	 */
	public function executeProcedure($name, $params)
	{
		if($this->isMySql())
		{
			return "call $name($params)";
		}
		if($this->isPgSql())
		{
			return "select $name($params)";
		}
		return null;
	}

	/**
	 * Create last ID statement
	 *
	 * @return self
	 */
	public function lastID()
	{
		if($this->isMySql())
		{
			$this->buffer .= "last_insert_id()\r\n";
		}
		if($this->isPgSql())
		{
			$this->buffer .= "lastval()\r\n";
		}
		return $this;

	}

	/**
	 * Create current date statement
	 *
	 * @return string|null
	 */
	public function currentDate()
	{
		if($this->isMySql())
		{
			return "CURRENT_DATE";
		}
		if($this->isPgSql())
		{
			return "CURRENT_DATE";
		}
		return null;
	}
	
	/**
	 * Create current time statement
	 *
	 * @return string
	 */
	public function currentTime()
	{
		if($this->isMySql())
		{
			return "CURRENT_TIME";
		}
		if($this->isPgSql())
		{
			return "CURRENT_TIME";
		}
		return null;
	}
	
	/**
	 * Create current date time statement
	 *
	 * @return string|null
	 */
	public function currentTimestamp()
	{
		if($this->isMySql())
		{
			return "CURRENT_TIMESTAMP";
		}
		if($this->isPgSql())
		{
			return "CURRENT_TIMESTAMP";
		}
		return null;
	}
	
	/**
	 * Create cow statement
	 *
	 * @param integer $precission
	 * @return string
	 */
	public function now($precission = 0)
	{
		if($precission > 0)
		{
			if($precission > 6)
			{
				$precission = 6;
			}
			return "now($precission)";
		}
		else
		{
			return "now()";
		}
	}

	/**
	 * Escape SQL
	 *
	 * @param string $query
	 * @return string
	 */
	public function escapeSQL($query)
	{
		if(stripos($this->databaseType, "mysql") !== false || stripos($this->databaseType, "mariadb") !== false)
		{
			return str_replace(array("\r", "\n"), array("\\r", "\\n"), addslashes($query));
		}
		if(stripos($this->databaseType, "postgresql") !== false)
		{
			return str_replace(array("\r", "\n"), array("\\r", "\\n"), $this->replaceQuote($query));
		}
		else
		{
			return $query;
		}
	}

	/**
	 * Replace quote
	 * @param string $query
	 * @return string
	 */
	public function replaceQuote($query)
	{
		return str_replace("'", "''", $query); 
	}

	/**
	 * Get SQL query
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Get SQL query
	 *
	 * @return string
	 */
	public function toString()
	{
		$sql = $this->buffer;
		if($this->limitOffset)
		{
			if($this->isMySql())
			{
				$sql .= "limit ".$this->offset.", ".$this->limit;
			}
			else if($this->isPgSql())
			{
				$sql .= "limit ".$this->limit." offset ".$this->offset;
			}
		}
		return $sql;
	}

	/**
	 * Get the value of databaseType
	 */ 
	public function getDatabaseType()
	{
		return $this->databaseType;
	}
}
