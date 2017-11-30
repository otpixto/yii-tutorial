<?php

namespace App\Http\Controllers;

use App\Classes\Title;

class HomeController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Главная' );
    }


    public function index ()
    {

        return redirect()->route( 'tickets.index' );

    }

    public function about ()
    {

        return view('home' )
            ->with( 'title', 'О компании' );

    }

    public function getFile ()
    {

        return view('home' )
            ->with( 'title', 'Главная' );

    }

}
