<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Entity\Song;
use Pico\File\FileMp3;
use Pico\File\FileUpload;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = $database->generateNewId();

$randomSongId = trim(@$_POST['random_song_id']);

try
{
    $response = new Song(null, $database);
    $response->setSongId($id);
    $response->setRandomSongId($randomSongId);
    $response->setTimeCreate(date('Y-m-d H:i:s'));
    $response->setIpCreate($_SERVER['REMOTE_ADDR']);
    $response->setAdminCreate('1');
    $response->insert();

    // get uploaded file path
    $fileUpload = new FileUpload();
    $targetDir = dirname(__DIR__)."/files";
    $path = $fileUpload->upload($_FILES, 'file', $targetDir, $id);
    $response->setFilePath($path);
    $response->setFileName(basename($path));

    // get MP3 duration
    $mp3file = new FileMp3($path); 
    $duration = $mp3file->getDuration(); 
    $response->setDuration($duration);

    $response->update();

    $restResponse = new PicoResponse();
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);

}
catch(Exception $e)
{
    // do nothing
}
