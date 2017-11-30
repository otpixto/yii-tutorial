<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Регионы' );
    }

    public function index ()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $regions = Region
            ::orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $regions
                ->where( 'name', 'like', $s );
        }

        $regions = $regions->paginate( 30 );

        return view('admin.regions.index' )
            ->with( 'regions', $regions );

    }

    public function show ( $id )
    {
        return redirect()->route( 'regions.index' );
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать регион' );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        return view('admin.regions.edit' )
            ->with( 'region', $region );

    }

    public function create ()
    {

        Title::add( 'Создать регион' );

        return view('admin.regions.create' );

    }

    public function update ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $this->validate( $request, Region::$rules );

        $region->fill( $request->all() );
        $region->save();

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно отредактирован' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, Region::$rules );

        $region = Region::create( $request->all() );

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно добавлен' );

    }

}
