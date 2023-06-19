<?php

use Pico\Constants\PicoHttpStatus;
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Response\PicoResponse;

require_once dirname(__DIR__)."/inc/auth.php";

$song_id = trim(@$_GET['song_id']);
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder
    ->newQuery()
    ->select("song.song_id as id, song.name as value, song.lyric as lyric")
    ->from("song")
    ->where("song.song_id = ? and song.active = ? ", $song_id, true);
$response = $database->fetch($queryBuilder, PDO::FETCH_ASSOC);

$restResponse = new PicoResponse();
$restResponse->sendResponse($response, 'json', null, PicoHttpStatus::HTTP_OK);