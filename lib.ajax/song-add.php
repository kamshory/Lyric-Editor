<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Entity\Song;
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Exceptions\NoRecordFoundException;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$songId = $database->generateNewId();

$inputPost = new PicoRequest(INPUT_POST);
$inputPost->filterTitle(FILTER_SANITIZE_SPECIAL_CHARS);

$randomSongId = $inputPost->getRandomSongId();

$data = new Song();
if(!empty($randomSongId))
{
    $savedData = new Song(null, $database);
    try
    {
        $saved = $savedData->findByRandomSongId($randomSongId);
        $data = $saved[0];
        $songId = $data->getSongId();
    }
    catch(NoRecordFoundException $e)
    {
        // do nothing
        $data = new Song($inputPost, $database); 
        $data->setSongId($songId);
        $data->setActive(true);    
    }
}
else
{
    $data = new Song($_POST, $database);
    $data->setSongId($songId);
    $data->setActive(true);
}

try
{
    $postData = $data->removePropertyExcept($inputPost, 
        array(
            'title',
            'artist_vocal',
            'artist_composer',
            'artist_arranger',
            'album_id',
            'genre_id'
        )
    );

    foreach($postData as $key=>$val)
    {
        $data->set($key, $val);
    }  
    
    $data->setTimeEdit(date('Y-m-d H:i:s'));
    $data->setIpEdit($_SERVER['REMOTE_ADDR']);
    $data->setAdminEdit('1');

    $data->save();

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
