<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Traits\LogsTrait;

class BaseController extends Controller
{

    use LogsTrait;

    public function __construct ()
    {
        $this->middleware('auth' );
    }

    protected function clearCache ()
    {
        \Cache::tags( 'tickets' )->flush();
    }

    public function clearCacheAndRedirect ()
    {
        $this->clearCache();
        return redirect()->back()->with( 'success', 'Кеш успешно сброшен' );
    }

}
