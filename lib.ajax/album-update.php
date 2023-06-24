<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$album_id = htmlspecialchars(trim(@$_POST['album_id']));
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
    $data->update();
    $restResponse = new PicoResponse();
    $response = AlbumDto::valueOf($data);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
