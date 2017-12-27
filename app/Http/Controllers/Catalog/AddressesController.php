<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Address;
use App\Models\AddressManagement;
use App\Models\Category;
use App\Models\Management;
use App\Models\Region;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class AddressesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Здания' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $region = $request->get( 'region' );

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $addresses = Address
            ::mine()
            ->orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $addresses
                ->where( 'name', 'like', $s )
                ->orWhere( 'guid', 'like', $s );
        }

        if ( !empty( $region ) )
        {
            $addresses
                ->where( 'region_id', '=', $region );
        }

        $addresses = $addresses
            ->paginate( 30 )
            ->appends( $request->all() );

        return view( 'catalog.addresses.index' )
            ->with( 'addresses', $addresses )
            ->with( 'regions', $regions );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        Title::add( 'Добавить здание' );

        $managements = Management::orderBy( 'name' )->pluck( 'name', 'id' );

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.addresses.create' )
            ->with( 'managements', $managements )
            ->with( 'regions', $regions );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'guid'                  => 'nullable|unique:addresses,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'region_id'             => 'required|integer',
            'name'                  => 'required|string|max:255',
        ];

        $this->validate( $request, $rules );

        $address = Address::create( $request->all() );
        if ( $address instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $address );
        }
        $address->save();

        return redirect()->route( 'addresses.index' )
            ->with( 'success', 'Адрес успешно добавлен' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        Title::add( 'Редактировать здание' );

        $address = Address::with( 'managements' )->find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $addressManagements = $address->managements()
            ->orderBy( 'name' )
            ->get();

        $addressTypes = $address->types()
            ->orderBy( 'name' )
            ->get();

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.addresses.edit' )
            ->with( 'address', $address )
            ->with( 'addressManagements', $addressManagements )
            ->with( 'addressTypes', $addressTypes )
            ->with( 'regions', $regions );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $address = Address::find( $id );
        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $this->validate( $request, Address::$rules );

        $res = $address->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        return redirect()->route( 'addresses.edit', $address->id )
            ->with( 'success', 'Адрес успешно отредактирован' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $region_id = $request->get( 'region_id', Region::current()->first()->id );

        $addresses = Address
            ::select(
                'id',
                'name AS text'
            )
            ->where( 'name', 'like', $s )
            ->whereHas( 'region', function ( $q ) use ( $region_id )
            {
                return $q
                    ->mine()
                    ->where( 'id', '=', $region_id );
            })
            ->orderBy( 'name' )
            ->get();

        return $addresses;

    }

    public function getAddManagements ( Request $request )
    {
        $address = Address::find( $request->get( 'id' ) );
        if ( ! $address )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Адрес не найден' );
        }
        $allowedManagements = Management
            ::mine()
            ->whereNotIn( 'id', $address->managements->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        return view( 'catalog.addresses.add_managements' )
            ->with( 'address', $address )
            ->with( 'allowedManagements', $allowedManagements );
    }

    public function postAddManagements ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнители успешно добавлены' );

    }

    public function getAddTypes ( Request $request )
    {
        $address = Address::find( $request->get( 'id' ) );
        if ( ! $address )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Адрес не найден' );
        }
        $allowedTypes = Type
            ::whereNotIn( 'id', $address->types->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        return view( 'catalog.addresses.add_types' )
            ->with( 'address', $address )
            ->with( 'allowedTypes', $allowedTypes );
    }

    public function postAddTypes ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->types()->attach( $request->get( 'types', [] ) );

        return redirect()->back()
            ->with( 'success', 'Типы успешно назначены' );

    }

    public function delManagement ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->managements()->detach( $request->get( 'management_id' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнитель успешно удален' );

    }

    public function delType ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->types()->detach( $request->get( 'type_id' ) );

        return redirect()->back()
            ->with( 'success', 'Тип успешно удален' );

    }

}
