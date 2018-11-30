<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Http\Controllers\Controller;
use App\Traits\LogsTrait;

class BaseController extends Controller
{

    use LogsTrait;

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Справочник' );
    }

    protected function clearCache ()
    {
        \Cache::tags( 'catalog' )->flush();
    }

    public function clearCacheAndRedirect ()
    {
        $this->clearCache();
        return redirect()->back()->with( 'success', 'Кеш успешно сброшен' );
    }

}
