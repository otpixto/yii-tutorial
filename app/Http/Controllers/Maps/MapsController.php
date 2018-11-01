<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Models\Log;

class MapsController extends BaseController
{

    public function tickets ()
    {
        Title::add( 'География обращений' );
        $this->addLog( 'Просмотрел карту обращений' );
        return view( 'maps.tickets' );
    }

    public function works ()
    {
        Title::add( 'География отключений' );
        $this->addLog( 'Просмотрел карту отключений' );
        return view( 'maps.works' );
    }

}
