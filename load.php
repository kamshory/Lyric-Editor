<?php

use Pico\Data\Song;

require_once "inc/auth.php";

$song_id = $_GET['song_id'];
$song = new Song(null, $database);
$song->setSongId($song_id);
$song->select();