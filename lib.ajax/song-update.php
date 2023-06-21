<?php

use Pico\Constants\PicoHttpStatus;

use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Exception\NoRecordFoundException;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = (trim(@$_POST['id']));
$title = htmlspecialchars(trim(@$_POST['title']));
$active = trim(@$_POST['active']) == '1' || trim(@$_POST['active']) == 'true';

$artist_vocal = htmlspecialchars(trim(@$_POST['artist_vocal']));
$artist_composer = htmlspecialchars(trim(@$_POST['artist_composer']));
$artist_arranger = htmlspecialchars(trim(@$_POST['artist_arranger']));
$album_id = htmlspecialchars(trim(@$_POST['album_id']));
$genre_id = htmlspecialchars(trim(@$_POST['genre_id']));

if(empty($id) || empty($title))
{
    exit();
}

try
{
    $data->setSongId($id);
    $data->setTitle($title);
    $data->setArtistVocal($artist_vocal);
    $data->setArtistComposer($artist_composer);
    $data->setArtistAranger($artist_arranger);
    $data->setAlbumId($album_id);
    $data->setGenreId($genre_id);

    $data->setTimeEdit(date('Y-m-d H:i:s'));
    $data->setIpEdit($_SERVER['REMOTE_ADDR']);
    $data->setAdminEdit('1');

    $data->update();

    $restResponse = new PicoResponse();    
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
    $sql = $queryBuilder->newQuery()
        ->select("song.*, 
        (select artist.name from artist where artist.artist_id = song.artist_vocal) as artist_vocal_name,
        (select artist.name from artist where artist.artist_id = song.artist_composer) as artist_composer_name,
        (select artist.name from artist where artist.artist_id = song.artist_arranger) as artist_arranger_name,
        (select genre.name from genre where genre.genre_id = song.genre_id) as genre_name,
        (select album.name from album where album.album_id = album.album_id) as album_name
        ")
        ->from("song")
        ->where("song.song_id = ? ", $data->getSongId())
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
