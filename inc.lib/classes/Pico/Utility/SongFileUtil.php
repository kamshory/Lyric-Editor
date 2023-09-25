<?php

namespace Pico\Utility;

class SongFileUtil
{
    public static function isMp3File($data)
    {
        return true;
    }
    public static function isMidiFile($data)
    {
        return true;
    }
    public static function isXmlMusicFile($data)
    {
        return true;
    }
    
    public static function getContent($path, $max = 0)
    {
        $fsize = filesize($path);
        if($max > $fsize)
        {
            $max = $fsize;
        }
        $handle = fopen($path, "rb");
        $contents = fread($handle, $max);
        fclose($handle);
        return $contents;
    }
    
    public static function saveMidiFile($songId, $content)
    {
        return "";
    }
    
    public static function saveXmlMusicFile($songId, $content)
    {
        return "";
    }
}