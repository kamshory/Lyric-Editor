<?php
namespace Pico\Data;

use Pico\DynamicObject\DynamicObject;

/**
 * @Table (name=album)
 */
class Album extends DynamicObject
{
    /**
     * Album ID
     *
     * @var string
     * @Column (name=album_id)
     * @Id
     */
    protected $albumId;

    /**
     * Title
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