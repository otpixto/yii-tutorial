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
        //(new GzhiHandler())->curlGetFile();
        //( new GzhiHandler() )->fillExportedTickets();
        //( new GzhiHandler() )->sendGzhiInfo();
        $data = getrusage();
        dd( $data );
    }


    public function testExportGzhiTickets ()
    {
        ( new GzhiHandler() )->exportGzhiTickets();
    }

    public function testFillExportedTickets ()
    {
        ( new GzhiHandler() )->fillExportedTickets();
    }

    public function testSendGzhiInfo ()
    {
        ( new GzhiHandler() )->sendGzhiInfo();
    }

}
