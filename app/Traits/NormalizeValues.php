<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait NormalizeValues
{
    public static function normalizeValues ( array & $attributes = [] )
    {
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone' ] ), - 10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone2' ] ), - 10 );
        }
        if ( ! empty( $attributes[ 'firstname' ] ) )
        {
            $attributes[ 'firstname' ] = Str::ucfirst( Str::lower( $attributes[ 'firstname' ] ) );
        }
        if ( ! empty( $attributes[ 'middlename' ] ) )
        {
            $attributes[ 'middlename' ] = Str::ucfirst( Str::lower( $attributes[ 'middlename' ] ) );
        }
        if ( ! empty( $attributes[ 'lastname' ] ) )
        {
            $attributes[ 'lastname' ] = Str::ucfirst( Str::lower( $attributes[ 'lastname' ] ) );
        }
    }
}
