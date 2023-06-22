<?php
namespace Pico\Database;

class PicoDatabaseQueryBuilder // NOSONAR
{
	private $buffer = "";
	private $limitOffset = false;
	private $limit = 0;
	private $offset = 0;
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

	public function newQuery()
	{
		$this->buffer = "";
		$this->limitOffset = false;
		return $this;
	}

	public function insert()
	{
		$this->buffer = "insert \r\n";
		return $this;
	}

	public function into($query)
	{
		$this->buffer .= "into $query\r\n";
		return $this;
	}
	public function select($query = "")
	{
		$this->buffer .= "select $query\r\n";
		return $this;
	}
	public function alias($query)
	{
		$this->buffer .= "as $query\r\n";
		return $this;
	}
	public function fields($query)
	{
		$this->buffer .= "$query \r\n";
		return $this;
	}
	public function values($query)
	{
		$this->buffer .= "values $query \r\n";
		return $this;
	}
	public function delete()
	{
		$this->buffer .= "delete \r\n";
		return $this;
	}
	public function from($query)
	{
		$this->buffer .= "from $query \r\n";
		return $this;
	}
	public function join($query)
	{
		$this->buffer .= "join $query \r\n";
		return $this;
	}
	public function innerJoin($query)
	{
		$this->buffer .= "inner join $query \r\n";
		return $this;
	}
	public function outerJoin($query)
	{
		$this->buffer .= "outer join $query \r\n";
		return $this;
	}
	public function leftJoin($query)
	{
		$this->buffer .= "left join $query \r\n";
		return $this;
	}
	public function rightJoin($query)
	{
		$this->buffer .= "right join $query \r\n";
		return $this;
	}

	public function on($query)
	{
		$this->buffer .= "on $query \r\n";
		return $this;
	}

	public function update($query)
	{
		$this->buffer .= "update $query \r\n";
		return $this;
	}

	public function set($query)
	{
		$this->buffer .= "set $query \r\n";
		return $this;
	}

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
			$buffer = $this->createFilter($params);
			$this->buffer .= "where $buffer \r\n";
		}
		else
		{
			$this->buffer .= "where $query \r\n";
		}		
		return $this;
	}

	public function createFilter($args)
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

	public function having($query)
	{
		$this->buffer .= "having $query \r\n";
		return $this;
	}

	public function orderBy($query)
	{
		$this->buffer .= "order by $query \r\n";
		return $this;
	}

	public function groupBy($query)
	{
		$this->buffer .= "group by $query \r\n";
		return $this;
	}

	public function limit($limit)
	{
		$this->limitOffset = true;
		$this->limit = $limit;
		return $this;
	}

	public function offset($offset)
	{
		$this->limitOffset = true;
		$this->offset = $offset;
		return $this;
	}

	public function lockTables($tables)
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "lock tables $tables";
		}
		if($this->databaseType == "postgresql")
		{
			return "lock tables $tables";
		}
	}

	public function unlockTables()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "unlock tables";
		}
		if($this->databaseType == "postgresql")
		{
			return "unlock tables";
		}
	}

	public function startTransaction()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "start transaction";
		}
		if($this->databaseType == "postgresql")
		{
			return "start transaction";
		}
	}

	public function commit()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "commit";
		}
		if($this->databaseType == "postgresql")
		{
			return "commit";
		}
	}
	public function rollback()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "rollback";
		}
		if($this->databaseType == "postgresql")
		{
			return "rollback";
		}
	}
	public function executeFunction($name, $params)
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "select $name($params)";
		}
		if($this->databaseType == "postgresql")
		{
			return "select $name($params)";
		}
	}
	public function executeProcedure($name, $params)
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "call $name($params)";
		}
		if($this->databaseType == "postgresql")
		{
			return "select $name($params)";
		}
	}
	public function lastID()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			$this->buffer .= "last_insert_id()\r\n";
		}
		if($this->databaseType == "postgresql")
		{
			$this->buffer .= "lastval()\r\n";
		}
		return $this;

	}
	public function currentDate()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "CURRENT_DATE";
		}
		if($this->databaseType == "postgresql")
		{
			return "CURRENT_DATE";
		}
	}
	
	public function currentTime()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "CURRENT_TIME";
		}
		if($this->databaseType == "postgresql")
		{
			return "CURRENT_TIME";
		}
	}
	
	public function currentTimestamp()
	{
		if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
		{
			return "CURRENT_TIMESTAMP";
		}
		if($this->databaseType == "postgresql")
		{
			return "CURRENT_TIMESTAMP";
		}
	}
	
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
	public function replaceQuote($query)
	{
		$query = str_replace("'", "''", $query); // NOSONAR
		return $query;
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
			if($this->databaseType == "mysql" || $this->databaseType == "mariadb")
			{
				$sql .= "limit ".$this->offset.", ".$this->limit;
			}
			else if($this->databaseType == "postgresql")
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
