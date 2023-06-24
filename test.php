<?php

use Pico\Data\Dto\ArtistDto;
use Pico\Data\Entity\Artist;

require_once "inc/auth.php";

$artist = new Artist();
$artistDto = ArtistDto::valueOf($artist);
$artistDto->setCobaSajaYa("AAAAAAAAAAAAAAAA");
echo $artistDto;
print_r($artistDto->value(false));
