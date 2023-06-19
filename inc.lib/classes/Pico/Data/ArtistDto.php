<?php

namespace Pico\Data;

use Pico\DynamicObject\SetterGetter;

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
        $output = new ArtistDto();
        $output->setArtistId($input->getArtistId());
        $output->setName($input->getName());
        $output->setActive($input->getActive());        
        return $output;
    }
}
