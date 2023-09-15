<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$inputPost = new PicoRequest(INPUT_POST);
$inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$data = new Album($inputPost, $database);
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
