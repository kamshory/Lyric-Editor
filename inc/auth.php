<?php

use Pico\Config\ConfigApp;
use Pico\Database\PicoDatabase;
use Pico\Database\PicoDatabaseCredentials;

require_once dirname(__DIR__)."/inc.lib/vendor/autoload.php";

$cfg = new ConfigApp(null, null, true);
$cfg->loadIniFile(dirname(dirname(__DIR__))."/app.ini");

$databaseCredentials = new PicoDatabaseCredentials();

$databaseCredentials->loadIniFile(dirname(dirname(__DIR__))."/db.ini");

$database = new PicoDatabase($databaseCredentials);

try
{
    $database->connect();
}
catch(Exception $e)
{
    // do nothing
}