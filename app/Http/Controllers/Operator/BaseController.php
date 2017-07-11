<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Обращения' );
    }

}
