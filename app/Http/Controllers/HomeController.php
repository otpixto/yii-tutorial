<?php

namespace App\Http\Controllers;

use App\Classes\GzhiHandler;
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

    public function blank ()
    {
        return view('blank' );
    }

    public function getFile ()
    {
        return view('home' )
            ->with( 'title', 'Главная' );
    }

    public function test ()
    {
        //(new GzhiHandler())->exportGzhiTickets();
        //(new GzhiHandler())->getOrgList();
        $data = getrusage();
        dd( $data );
    }

}
