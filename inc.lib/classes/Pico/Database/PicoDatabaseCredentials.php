<?php

namespace Pico\Database;
use Pico\DynamicObject\DynamicObject;

class PicoDatabaseCredentials extends DynamicObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}