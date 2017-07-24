<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    private $guards = null;

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Адиминистрирование' );
    }

    protected function getGuards ( $flush = false )
    {
        if ( $flush || is_null( $this->guards ) )
        {
            foreach ( config( 'auth.guards' ) as $guard => $_ )
            {
                $this->guards[ $guard ] = $guard;
            }
        }
        return $this->guards;
    }

}
