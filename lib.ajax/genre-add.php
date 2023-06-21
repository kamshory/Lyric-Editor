<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\GenreDto;
use Pico\Data\Entity\Genre;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = $database->generateNewId();
$name = htmlspecialchars(trim(@$_POST['name']));

if(empty($name))
{
    exit();
}

$data = new Genre(null, $database);
$data->setGenreId($id);
$data->setName($name);
$data->setActive(true);
try
{
    $saved = $data->findByName($name);
    if($saved && !empty($saved))
    {
       $data->setGenreId($saved[0]->getGenreId());
    }
    else
    {
        $data->save();
    }
    $restResponse = new PicoResponse();
    $response = GenreDto::valueOf($data);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
