<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Адиминистрирование' );
    }

}
