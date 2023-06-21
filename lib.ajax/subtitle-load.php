<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Song;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

try
{
    $song = new Song(array('song_id'=>trim(@$_GET['song_id'])), $database);
    $song->select();
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