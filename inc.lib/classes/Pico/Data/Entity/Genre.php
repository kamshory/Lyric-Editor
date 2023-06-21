<?php
namespace Pico\Data\Entity;

use Pico\DynamicObject\DynamicObject;

/**
 * Genre
 * @Table (name=genre)
 */
class Genre extends DynamicObject
{    
    /**
     * Genre ID
     *
     * @var string
     * @Column (name=genre_id)
     * @Id
     */
    protected $genreId;
    
    /**
     * Name
     *
     * @var string
     * @Column (name=name)
     */
    protected $name;

    /**
     * Active
     *
     * @var bool
     * @Column (name=active)
     */
    protected $active;
}