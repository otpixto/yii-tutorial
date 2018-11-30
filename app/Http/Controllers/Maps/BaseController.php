<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Http\Controllers\Controller;
use App\Traits\LogsTrait;

class BaseController extends Controller
{

    use LogsTrait;

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Карты' );
    }

    protected function clearCache ()
    {
        \Cache::tags( 'maps' )->flush();
    }

    public function clearCacheAndRedirect ()
    {
        $this->clearCache();
        return redirect()->back()->with( 'success', 'Кеш успешно сброшен' );
    }

}
