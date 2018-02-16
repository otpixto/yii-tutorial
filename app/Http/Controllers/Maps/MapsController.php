<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;

class MapsController extends BaseController
{

    public function tickets ()
    {
        Title::add( 'География обращений' );
        return view( 'maps.tickets' );
    }

    public function works ()
    {
        Title::add( 'География работ на сетях' );
        return view( 'maps.works' );
    }

}
