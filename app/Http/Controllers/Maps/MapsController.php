<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Models\Log;

class MapsController extends BaseController
{

    public function tickets ()
    {
        Title::add( 'География обращений' );
        $log = Log::create([
            'text' => 'Просмотрел карту обращений'
        ]);
        $log->save();
        return view( 'maps.tickets' );
    }

    public function works ()
    {
        Title::add( 'География отключений' );
        $log = Log::create([
            'text' => 'Просмотрел карту отключений'
        ]);
        $log->save();
        return view( 'maps.works' );
    }

}
