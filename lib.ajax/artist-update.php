<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\ArtistDto;
use Pico\Data\Entity\Artist;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$inputPost = new PicoRequest(INPUT_POST);
$artist = new Artist($inputPost, $database);

try
{
    $artist->update();
    $restResponse = new PicoResponse();
    $response = ArtistDto::valueOf($artist);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
