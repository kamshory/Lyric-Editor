<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Entity\Song;
use Pico\File\FileMp3;
use Pico\File\FileUpload;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$inputPost = new PicoRequest(INPUT_POST);

$id = $inputPost->getSongId();
if(empty($id))
{
    $id = $database->generateNewId();
}
$randomSongId = $inputPost->getRandomSongId();

try
{
    $song = new Song(null, $database);
    $song->setActive(true);
    $song->setSongId($id);

    $now = date('Y-m-d H:i:s');


    $song->setRandomSongId($randomSongId);
    $song->setTimeCreate($now);
    $song->setTimeEdit($now);
    $song->setIpCreate($_SERVER['REMOTE_ADDR']);
    $song->setIpEdit($_SERVER['REMOTE_ADDR']);
    $song->setAdminCreate('1');
    $song->setAdminEdit('1');

    // get uploaded file properties
    $fileUpload = new FileUpload();
    $targetDir = dirname(__DIR__)."/files";
    $fileUpload->upload($_FILES, 'file', $targetDir, $id);
    $path = $fileUpload->getFilePath();
    $song->setFileUploadTime($now);
    $song->setFilePath($path);
    $song->setFileName(basename($path));
    $song->setFileSize($fileUpload->getFileSize());
    $song->setFileType($fileUpload->getFileType());
    $song->setFileExtension($fileUpload->getFileExtension());
    $song->setFileMd5(md5_file($path));
    
    // get MP3 duration
    $mp3file = new FileMp3($path); 
    $duration = $mp3file->getDuration(); 
    $song->setDuration($duration);
    $song->save();

    $restResponse = new PicoResponse();
    $restResponse->sendResponse($song, 'json', null, PicoHttpStatus::HTTP_OK);

}
catch(Exception $e)
{
    // do nothing
}
