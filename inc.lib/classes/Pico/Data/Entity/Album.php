<?php
namespace Pico\Data\Entity;

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
     * Release date
     *
     * @var string
     * @Column (name=release_date)
     */
    protected $releaseDate;

    /**
     * Number of song
     *
     * @var integer
     * @Column (name=number_of_song)
     * @Value (minimum=0, default=0)
     */
    protected $numberOfSong;

    /**
     * Total duration
     *
     * @var float
     * @Column (name=duration)
     * @Value (minimum=0, default=0)
     */
    protected $duration;

    /**
     * Active
     *
     * @var bool
     * @Column (name=active)
     */
    protected $active;
}