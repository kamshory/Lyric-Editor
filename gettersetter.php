<?php

use Pico\Data\Album;
use Pico\Data\AlbumDto;

require_once "inc/auth.php";

/*
$albumDto = new AlbumDto();
$albumDto->setAlbumId('1111');
$albumDto->setName('Nama');
$albumDto->setActive(true);
$albumDto->setApa('Apa');
*/

$album = new Album(null, $database);
$album->setAlbumId('1111');
$album->setName('Nama');
$album->setActive(true);
$album->setApa('Apa');
$albumDto = AlbumDto::valueOf($album);

echo $albumDto."\r\n";
