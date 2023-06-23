<?php
namespace Pico\Data\Entity;

use Pico\DynamicObject\DynamicObject;

/**
 * Artist
 * @Entity
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
     * Gender
     *
     * @var string
     * @Column (name=gender)
     */
    protected $gender;

    /**
     * Birth day
     *
     * @var string
     * @Column (name=birth_day)
     */
    protected $birthDay;

    /**
     * Active
     *
     * @var bool
     * @Column (name=active)
     */
    protected $active;
}