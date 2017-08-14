<?php

namespace App\Classes;

class Title
{

    private static $title = [];

    public static function add ( $value )
    {
        self::$title[] = $value;
    }

    public static function clear ()
    {
        self::$title = [];
    }

    public static function set ( $value )
    {
        self::clear();
        self::add( $value );
    }

    public static function get ()
    {
        return self::$title[ count( self::$title ) - 1 ] ?? null;
    }

    public static function render ( $html = false )
    {
        $title = config( 'app.name' );
        if ( count( self::$title ) != 0 )
        {
            $title .= ' - ' . implode( ' - ', self::$title );
            if ( !$html )
            {
                $title = strip_tags( $title );
            }
        }
        return $title;
    }

}