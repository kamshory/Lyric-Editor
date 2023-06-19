<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Album;
use Pico\Data\AlbumDto;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = $database->generateNewId();
$name = htmlspecialchars(trim(@$_POST['name']));

if(empty($name))
{
    exit();
}

$data = new Album(null, $database);
$data->setAlbumId($id);
$data->setName($name);
$data->setActive(true);
try
{
    $saved = $data->findByName($name);
    if($saved && !empty($saved))
    {
        $data->setAlbumId($saved[0]->getAlbumId());
    }
    else
    {
        $data->save();
    }
    $restResponse = new PicoResponse();
    
    $response = AlbumDto::valueOf($data);
    
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
