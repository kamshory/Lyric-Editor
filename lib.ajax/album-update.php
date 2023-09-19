<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\Song;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$inputPost = new PicoRequest(INPUT_POST);
$inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$inputPost->filterDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$inputPost->checkboxActive(false);
$album = new Album($inputPost, $database);

try
{
    $song = new Song(null, $database);
    
    try
    {   
        $songs = $song->findByAlbumId($album->getAlbumId());
        $duration = 0;
        $number_of_song = 0;
        foreach($songs->getResult() as $val)
        {
            $duration += $val->getDuration();
            $number_of_song ++;
        }

        $album->setDuration($duration);
        $album->setNumberOfSong($number_of_song);
    }
    catch(Exception $e)
    {
        $album->setDuration(0);
        $album->setNumberOfSong(0);        
    }
    
    $now = date('Y-m-d H:i:s');
    $album->setTimeEdit($now);
    $album->setIpEdit($_SERVER['REMOTE_ADDR']);
    $album->setAdminEdit(1);

    $album->update();
    $restResponse = new PicoResponse();
    $restResponse->sendResponse($album, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
