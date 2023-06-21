<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Response\PicoResponse;
use Pico\Song\PicoSong;

require_once dirname(__DIR__)."/inc/auth.php";

$songLoader = new PicoSong($database);
try
{
    $song = $songLoader->getSong(trim(@$_GET['song_id']));
    if($song != null)
    {
        $response = array(
            'song_id'=>$song->getSongId(),
            'lyric'=>$song->getLyric()
        );
        $restResponse = new PicoResponse();
        $restResponse->sendResponseJSON($response, null, PicoHttpStatus::HTTP_OK);
    }
}
catch(Exception $e)
{
    
}