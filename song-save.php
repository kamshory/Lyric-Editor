<?php

use Pico\Song\PicoSong;

require_once "inc/auth.php";
$song_id = $_POST['song_id'];
$songLoader = new PicoSong($database);
$song = $songLoader->getSong($song_id);

$text = $song->getSubtitle();
header("Content-type: text/plain");
header("Content-length: ".strlen($text));
echo $text;
