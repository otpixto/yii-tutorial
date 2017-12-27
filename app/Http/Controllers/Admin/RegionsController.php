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

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );

        $regions = Region
            ::orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $regions
                ->where( 'name', 'like', $s );
        }

        $regions = $regions
            ->paginate( 30 )
            ->appends( $request->all() );

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

        $rules = [
            'guid'                  => 'nullable|unique:regions,guid,' . $region->id . ',id|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
        ];

        if ( $request->has( 'name' ) || $request->has( 'domain' ) )
        {
            $rules += [
                'name'                  => 'required|string|max:255',
                'domain'                => 'required|string|max:100',
            ];
        }

        $this->validate( $request, $rules );

        $region->edit( $request->all() );

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно отредактирован' );

    }

    public function store ( Request $request )
    {

        $rules = [
            'guid'                  => 'nullable|unique:regions,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
            'name'                  => 'required|string|max:255',
            'domain'                => 'required|string|max:100',
        ];

        $this->validate( $request, $rules );

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
        $rules = [
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        ];
        $this->validate( $request, $rules );
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
