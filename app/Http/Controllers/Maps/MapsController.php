<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Models\Provider;
use App\Models\Type;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MapsController extends BaseController
{

    public function tickets ()
    {
        Title::add( 'География обращений' );
        $this->addLog( 'Просмотрел карту обращений' );
        return view( 'maps.tickets' );
    }

    public function works ( Request $request )
    {
        Title::add( 'География отключений' );
        $this->addLog( 'Просмотрел карту отключений' );
        $availableCategories = Type
            ::mine()
            ->where( 'works', '=', 1 )
            ->orderBy( Type::$_table . '.name' )
            ->pluck( Type::$_table . '.name', Type::$_table . '.id' )
            ->toArray();
        return view( 'maps.works' )
            ->with( 'category_id', $request->get( 'category_id' ) )
            ->with( 'availableCategories', $availableCategories );
    }

    public function positions ( Request $request )
    {
        Title::add( 'Где сотрудник' );
        $this->addLog( 'Просмотрел карту "Где сотрудник"' );
        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $res = User
            ::mine()
            ->whereNotNull( 'lon' )
            ->whereNotNull( 'lat' )
            ->whereNotNull( 'position_at' )
            ->whereHas( 'executor', function ( $executor )
            {
                return $executor
                    ->mine();
            })
            ->get();
        $availableUsers = [];
        foreach ( $res as $r )
        {
            $availableUsers[ $r->id ] = $r->getName();
        }
        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        return view( 'maps.positions' )
            ->with( 'availableUsers', $availableUsers )
            ->with( 'providers', $providers )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );
    }

}
