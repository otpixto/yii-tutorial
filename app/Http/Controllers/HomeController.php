<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct ()
    {
        $this->middleware('auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex ()
    {

        /*$file = File::first();
        $path = storage_path( $file->path );
        return response()->download( $path );*/

        return view('home' )
            ->with( 'title', 'Главная' );
    }
}
