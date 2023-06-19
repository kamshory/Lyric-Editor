<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Data\Artist;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";
$id = htmlspecialchars(trim(@$_POST['id']));
$name = htmlspecialchars(trim(@$_POST['name']));
$active = trim(@$_POST['active']) == '1' || trim(@$_POST['active']) == 'true';

if(empty($id) || empty($name))
{
    exit();
}

$data = new Artist(null, $database);
$data->setArtistId($id);
$data->setName($name);
$data->setActive($active);
try
{
    $data->update();
    $restResponse = new PicoResponse();
    $response = array('id'=>$id, 'value'=>$name, 'active'=>$active);
    $restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);
}
catch(Exception $e)
{
    // do nothing
}
