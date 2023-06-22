<?php

namespace Pico\Data\Dto;

use Pico\Data\Entity\Artist;
use Pico\DynamicObject\SetterGetter;

/**
 * Artist DTO
 * @JSON (property-naming-strategy=SNAKE_CASE)
 */
class ArtistDto extends SetterGetter
{
    /**
     * Artist ID
     *
     * @var string
     */
    protected $artistId;

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
     * Construct ArtistDto from Artist and not copy other properties
     *
     * @param Artist $input
     * @return self
     */
    public static function valueOf($input)
    {
        $output = new ArtistDto($input);
        $output->setArtistId($input->getArtistId());
        $output->setName($input->getName());
        $output->setActive($input->getActive());        
        return $output;
    }
}
