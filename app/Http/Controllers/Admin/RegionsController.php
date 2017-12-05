<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Region;
use App\Models\RegionPhone;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

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

        $region->edit( $request->all() );

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно отредактирован' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, Region::$rules );

        $region = Region::create( $request->all() );

        if ( $region instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $region );
        }

        $region->save();

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно добавлен' );

    }

    public function addRegionPhone ( Request $request, $id )
    {
        $this->validate( $request, Region::$rules_phone );
        $region = Region::find( $id );
        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }
        $phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $request->get( 'phone' ) ) ), -10 );
        $regionPhone = $region->addPhone( $phone );
        if ( $regionPhone instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $regionPhone );
        }
        $regionPhone->save();
        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Телефон успешно добавлен' );
    }

    public function delRegionPhone ( Request $request )
    {
        $RegionPhone = RegionPhone::find( $request->get( 'id' ) );
        $RegionPhone->delete();
    }

}
