<?php

namespace App\Classes;

class Segments
{

    protected static $cache_tags = 'segments';
    protected static $cache_life = 120;

    public static function clearCache ()
    {
        \Cache::tags( self::$cache_tags )->flush();
    }

}