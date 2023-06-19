<?php

use Pico\Config\ConfigApp;
use Pico\Data\Song as DataSong;
use Pico\DynamicObject\DynamicObject;

require_once "inc/auth.php";

$config = new ConfigApp(null, null, false);
$config->loadIniFile("db.ini");
$config->setDatabaseName('aaaaa');
$config->setApaSaja(new DynamicObject(array('param1' => 'value1', 'param2' => 2, 'param3' => 8.123, 'param4' => array(1, 'dua', array('tiga', 'empat', 5)))));
echo ($config);

exit();



$data = new stdClass;
$data->judule = "Opo";
$data->panjange = 188.67;
$data->tanggalRelease = "2023-06-10";
$data->artist_name = "AAAAAA";
$song = new DataSong(
    $data
);
echo $song . "\r\n";
