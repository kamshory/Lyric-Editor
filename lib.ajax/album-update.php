<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\Song;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$album_id = trim(@$_POST['album_id']);
$name = htmlspecialchars(trim(@$_POST['name']));
$release_date = trim(@$_POST['release_date']);
$active = trim(@$_POST['active']) == '1' || trim(@$_POST['active']) == 'true';

if(empty($album_id) || empty($name))
{
    exit();
}

$data = new Album(null, $database);
$data->setAlbumId($album_id);
$data->setName($name);
$data->setReleaseDate($release_date);
$data->setActive($active);

try
{
    $song = new Song(null, $database);
    $songs = $song->findByAlbumId($album_id);
    $duration = 0;
    $number_of_song = 0;
    foreach($songs as $val)
    {
        $duration += $val->getDuration();
        $number_of_song ++;
    }

    $data->setDuration($duration);
    $data->setNumberOfSong($number_of_song);

    $data->update();
    $restResponse = new PicoResponse();
    $response = AlbumDto::valueOf($data);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
