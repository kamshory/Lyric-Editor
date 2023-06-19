<?php

namespace Pico\Data;

use Pico\DynamicObject\SetterGetter;

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
        $output = new GenreDto();
        $output->setGenreId($input->getGenreId());
        $output->setName($input->getName());
        $output->setActive($input->getActive());        
        return $output;
    } 
}
