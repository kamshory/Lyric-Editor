<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\ArtistDto;
use Pico\Data\Entity\Artist;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = $database->generateNewId();
$name = htmlspecialchars(trim(@$_POST['name']));

if(empty($name))
{
    exit();
}

$data = new Artist(null, $database);
$data->setArtistId($id);
$data->setName($name);
$data->setActive(true);
try
{
    $saved = $data->findByName($name);
    if($saved && !empty($saved))
    {
        $data->setArtistId($saved[0]->getArtistId());
    }
    else
    {
        $data->save();
    }
    $restResponse = new PicoResponse();
    $response = ArtistDto::valueOf($data);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
