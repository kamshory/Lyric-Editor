<?php

use Pico\Data\Song;

require_once "inc/auth.php";

$song = new Song(array('song_id'=>'1111111', 'title'=>'coba', 'artist_vocal'=>'222222'), $database);
/*
$song
    ->setSongId('645640')
    ->setTitle('setTitle')
    ->setDuration(100.01)
    ->setJudul('setJudul')
    ->setTanggalRelease("2023-06-20")
;
*/
$song->withDatabase($database);

print_r($song->value());

echo "\r\n";

try
{
    $song->delete();
}
catch(Exception $e)
{
    echo $e->getMessage();
}

echo $database->getLastQuery();
