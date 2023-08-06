<?php

namespace Pico\Data\Entity;

use Pico\DynamicObject\DynamicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class Song extends DynamicObject
{
	/**
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @var string
	 */
	protected $songId;

	/**
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $randomSongId;

	/**
	 * @Column(name="title", type="text", nullable=true)
	 * @var string
	 */
	protected $title;

	/**
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $albumId;

	/**
	 * @Column(name="artist_vocal", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $artistVocal;

	/**
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * @Column(name="file_path", type="text", nullable=true)
	 * @var string
	 */
	protected $filePath;

	/**
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @var string
	 */
	protected $fileName;

	/**
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @var string
	 */
	protected $fileType;

	/**
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @var integer
	 */
	protected $fileSize;

	/**
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * @Column(name="file_upload_time", type="timestamp", length=19, nullable=true)
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * @Column(name="duration", type="float", nullable=true)
	 * @var double
	 */
	protected $duration;

	/**
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $genreId;

	/**
	 * @Column(name="lyric", type="longtext", nullable=true)
	 * @var string
	 */
	protected $lyric;

	/**
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @var string
	 */
	protected $comment;

	/**
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $active;

}