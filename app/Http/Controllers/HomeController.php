<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Classes\Title;

class HomeController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Главная' );
    }


    public function getIndex ()
    {

        return view('home' )
            ->with( 'title', 'Главная' );

    }

}
