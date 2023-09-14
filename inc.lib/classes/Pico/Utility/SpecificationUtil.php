<?php

namespace Pico\Utility;

use Pico\Database\PicoPredicate;
use Pico\Database\PicoSpecification;
use Pico\Request\PicoRequest;

class SpecificationUtil
{
    /**
     * Create MIDI specification
     * @param PicoRequest $name
     * @return PicoSpecification
     */
    public static function createMidiSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getMidiId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('midiId', $inputGet->getMidiId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateCenterLike($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistVocalId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalId', $inputGet->getArtistVocalId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }


    /**
     * Create Song specification
     * @param PicoRequest $name
     * @return PicoSpecification
     */
    public static function createSongSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getSongId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('songId', $inputGet->getSongId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateCenterLike($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistVocalId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalId', $inputGet->getArtistVocalId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create album specification
     * @param PicoRequest $name
     * @return PicoSpecification
     */
    public static function createAlbumSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateCenterLike($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create genre specification
     * @param PicoRequest $name
     * @return PicoSpecification
     */
    public static function createGenreSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateCenterLike($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create artist specification
     * @param PicoRequest $name
     * @return PicoSpecification
     */
    public static function createArtistsSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateCenterLike($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }
}