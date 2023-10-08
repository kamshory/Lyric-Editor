<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Entity\Song;
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$inputPost = new PicoRequest(INPUT_POST);
$inputPost->filterTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$inputPost->checkboxActive(false);

try
{
    $song = new Song($inputPost, $database);
    $song->update();

    $restResponse = new PicoResponse();    
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
    
    $sql = $queryBuilder->newQuery()
        ->select("song.*, 
        (select artist.name from artist where artist.artist_id = song.artist_vocal limit 0,1) as artist_vocal_name,
        (select artist.name from artist where artist.artist_id = song.artist_composer limit 0,1) as artist_composer_name,
        (select artist.name from artist where artist.artist_id = song.artist_arranger limit 0,1) as artist_arranger_name,
        (select genre.name from genre where genre.genre_id = song.genre_id limit 0,1) as genre_name,
        (select album.name from album where album.album_id = song.album_id limit 0,1) as album_name
        ")
        ->from("song")
        ->where("song.song_id = ? ", $song->getSongId())
        ;
        
    try
    {
        $record = $database->fetch($sql, PDO::FETCH_OBJ);
        $restResponse->sendResponseJSON($record, null, PicoHttpStatus::HTTP_OK);
    }
    catch(Exception $e)
    {
        // do nothing
    }
}
catch(Exception $e)
{
    // do nothing
}
