<?php
namespace Pico\Data;

use Pico\DynamicObject\DynamicObject;

/**
 * Artist
 * @Table (name=artist)
 */
class Artist extends DynamicObject
{    
    /**
     * Artis ID
     *
     * @var string
     * @Column (name=artist_id)
     * @Id
     */
    protected $artistId;
    
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