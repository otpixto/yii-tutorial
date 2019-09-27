<?php

namespace App\Classes;

class Segments
{

    protected static $cache_tags = 'segments';
    protected static $cache_life = 1440;

    public static function clearCache ()
    {
        \Cache::tags( self::$cache_tags )->flush();
    }

}
