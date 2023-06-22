<?php

namespace Pico\Data\Dto;

use Pico\Data\Entity\Genre;
use Pico\DynamicObject\SetterGetter;

/**
 * Genre DTO
 * @JSON (property-naming-strategy=SNAKE_CASE)
 */
class GenreDto extends SetterGetter
{
    /**
     * Genre ID
     *
     * @var string
     */
    protected $genreId;

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
     * Construct GenreDto from Genre and not copy other properties
     *
     * @param Genre $input
     * @return self
     */
    public static function valueOf($input)
    {
        $output = new GenreDto($input);
        $output->setGenreId($input->getGenreId());
        $output->setName($input->getName());
        $output->setActive($input->getActive());        
        return $output;
    } 
}
