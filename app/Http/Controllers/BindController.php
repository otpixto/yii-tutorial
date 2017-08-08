<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class BindsController extends Controller
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

    public function delete ( Request $request )
    {

        dd( $request->all() );

    }

}
