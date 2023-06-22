<?php

use Pico\Data\Entity\Song;

require_once "inc/auth.php";

$song = new Song();
$song->setCoba("AAAAAAAAAAAAAAAA");

echo($song);
