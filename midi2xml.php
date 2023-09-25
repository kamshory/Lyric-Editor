<?php

use Midi\Midi;

require_once __DIR__ . "/inc/app.php";


$midi = new Midi();
$midi->importMid('test.mid');

file_put_contents('test.xml', $midi->getXml());