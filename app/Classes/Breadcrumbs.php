<?php

namespace App\Classes;

class Breadcrumbs
{
    public static function render ( $breadcrumbs )
    {
        return view('parts.breadcrumbs' )
            ->with( 'breadcrumbs', $breadcrumbs );
    }
}