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

    public function error500 ()
    {
        return view( 'errors.500' );
    }

}
