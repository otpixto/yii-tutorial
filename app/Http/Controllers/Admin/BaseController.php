<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Traits\LogsTrait;

class BaseController extends Controller
{

    use LogsTrait;

    private $guards = null;

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Адиминистрирование' );
    }

    protected function getGuards ( $flush = false )
    {
        if ( $flush || is_null( $this->guards ) )
        {
            $guards = \DB::table( 'guards' )->get();
            foreach ( $guards as $guard )
            {
                $this->guards[ $guard->guard ] = $guard->guard;
            }
        }
        return $this->guards;
    }

    protected function clearCache ()
    {
        \Cache::flush();
    }

    public function clearCacheAndRedirect ()
    {
        $this->clearCache();
        return redirect()->back()->with( 'success', 'Кеш успешно сброшен' );
    }

    public function lonlat ()
    {
        $buildings = Building
            ::mine()
            ->whereNull( 'lon' )
            ->whereNull( 'lat' )
            ->get();
        foreach ( $buildings as $building )
        {
            $building->getCoordinates();
        }
        return redirect()
            ->to( '/' );
    }

}
