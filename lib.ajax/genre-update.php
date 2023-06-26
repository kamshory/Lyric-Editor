<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\GenreDto;
use Pico\Data\Entity\Genre;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$genre_id = htmlspecialchars(trim(@$_POST['genre_id']));
$name = htmlspecialchars(trim(@$_POST['name']));
$active = trim(@$_POST['active']) == '1' || trim(@$_POST['active']) == 'true';

if(empty($genre_id) || empty($name))
{
    exit();
}

$data = new Genre(null, $database);
$data->setGenreId($genre_id);
$data->setName($name);
$data->setActive($active);
try
{
    $data->update();
    $restResponse = new PicoResponse();
    $response = GenreDto::valueOf($data);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
