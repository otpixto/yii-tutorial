<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Models\Type;
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

    public function positions ()
    {
        Title::add( 'Где сотрудник' );
        //$this->addLog( 'Просмотрел карту отключений' );
        return view( 'maps.positions' );
    }

}
