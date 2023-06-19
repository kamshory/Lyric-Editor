<?php
namespace Pico\Data;

use Pico\DynamicObject\DynamicObject;

/**
 * Song
 * @Table (name=song)
 */
class Song extends DynamicObject
{
    /**
     * Song ID
     *
     * @var string
     * @Column (name=song_id)
     * @Id
     * @NotNull
     */
    protected $songId;    

    /**
     * Random song ID
     *
     * @var string
     * @Column (name=random_song_id)
     */
    protected $randomSongId;

    /**
     * Title
     *
     * @var string
     * @Column (name=title)
     */
    protected $title;

    /**
     * Album ID
     *
     * @var string
     * @Column (name=album_id)
     */
    protected $albumId;

    /**
     * Artist Vocal
     *
     * @var string
     * @Column (name=artist_vocal)
     */
    protected $artistVocal;

    /**
     * Artist Composer
     *
     * @var string
     * @Column (name=artist_composer)
     */
    protected $artistComposer;

    /**
     * Artist Arranger
     *
     * @var string
     * @Column (name=artist_arranger)
     */
    protected $artistArranger;

    /**
     * File path
     *
     * @var string
     * @Column (name=file_path)
     */
    protected $filePath;

    /**
     * File base name
     *
     * @var string
     * @Column (name=file_name)
     */
    protected $fileName;

    /**
     * File size
     *
     * @var integer
     * @Column (name=file_size)
     */
    protected $fileSize;

    /**
     * File MD5
     *
     * @var string
     * @Column (name=file_md5)
     */
    protected $fileMd5;

    /**
     * File size
     *
     * @var float
     * @Column (name=duration)
     */
    protected $duration;

    /**
     * Genre ID
     *
     * @var string
     * @Column (name=genre_id)
     */
    protected $genreId;

    /**
     * Lyric
     *
     * @var string
     * @Column (name=lyric)
     */
    protected $lyric;

    /**
     * Rate
     *
     * @var float
     * @Column (name=rate)
     */
    protected $rate;

    /**
     * Time create
     *
     * @var string
     * @Column (name=time_create)
     */
    protected $timeCreate;

    /**
     * Time edit
     *
     * @var string
     * @Column (name=time_edit)
     */
    protected $timeEdit;

    /**
     * IP create
     *
     * @var string
     * @Column (name=ip_create)
     */
    protected $ipCreate;

    /**
     * IP edit
     *
     * @var string
     * @Column (name=ip_edit)
     */
    protected $ipEdit;

    /**
     * Admin create
     *
     * @var string
     * @Column (name=admin_create)
     */
    protected $adminCreate;

    /**
     * Admin edit
     *
     * @var string
     * @Column (name=admin_edit)
     */
    protected $adminEdit;
  
    /**
     * Active
     * @var bool
     * @Column (name=active)
     */
    protected $active;
}