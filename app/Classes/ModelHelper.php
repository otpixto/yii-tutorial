<?php

namespace App\Classes;

class ModelHelper
{

    public static function getModels ( $path = null )
    {
        if ( is_null( $path ) )
        {
            $path = app_path( 'Models' );
        }
        $out = [];
        $files = scandir( $path );
        foreach ( $files as $file )
        {
            if ( $file == '.' || $file == '..' ) continue;
            $filename = $path . '/' . $file;
            if ( is_dir( $filename ) )
            {
                $out = array_merge( $out, self::getModels( $filename ) );
            }
            else
            {
                $model = mb_substr( $file, 0, - 4 );
                $out[] = $model;
            }
        }
        return $out;
    }

}