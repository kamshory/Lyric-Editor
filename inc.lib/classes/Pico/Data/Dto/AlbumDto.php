<?php

namespace Pico\Data\Dto;

use Pico\Data\Entity\Album;
use Pico\DynamicObject\SetterGetter;

/**
 * Album DTO
 * @JSON (property-naming-strategy=SNAKE_CASE)
 */
class AlbumDto extends SetterGetter
{
    /**
     * Album ID
     *
     * @var string
     */
    protected $albumId;

    /**
     * Title
     *
     * @var string
     */
    protected $name;

    /**
     * Active
     *
     * @var bool
     */
    protected $active;

    /**
     * Construct AlbumDto from Album and not copy other properties
     *
     * @param Album $input
     * @return self
     */
    public static function valueOf($input)
    {
        $output = new AlbumDto($input);
        $output->setAlbumId($input->getAlbumId());
        $output->setName($input->getName());
        $output->setActive($input->getActive());        
        return $output;
    }
}
