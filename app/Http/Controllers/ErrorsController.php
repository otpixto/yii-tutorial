<?php

namespace App\Http\Controllers;

use App\Classes\Title;

class ErrorsController extends Controller
{

    public function __construct ()
    {
        Title::add( 'Произошла ошибка' );
    }

    public function error404 ()
    {
        return view( 'errors.404' );
    }

    public function error403 ()
    {
        return view( 'errors.403' );
    }

    public function error423 ()
    {
        return view( 'errors.423' );
    }

    public function error429 ()
    {
        return view( 'errors.429' );
    }

    public function error500 ()
    {
        return view( 'errors.500' );
    }

}
