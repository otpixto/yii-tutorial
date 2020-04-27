<?php

namespace App\Classes;

use Illuminate\Http\Request;

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

    public static function getFalseRequestFromQueryString(string $queryString): Request {
        $urlDataArray = explode('&', $queryString);
        $requestArray = [];
        foreach ($urlDataArray as $urlDataItem) {
            $urlDataItemArray = explode('=', $urlDataItem);

            if (isset($urlDataItemArray[1]) && !empty($urlDataItemArray[1])) {
                $requestArray[$urlDataItemArray[0]] = $urlDataItemArray[1];
            }
        }

        return Request::create(
            '',
            'POST',
            $requestArray
        );
    }

}