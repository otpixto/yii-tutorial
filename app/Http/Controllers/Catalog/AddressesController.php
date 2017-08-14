<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Address;
use App\Models\AddressManagement;
use App\Models\Category;
use App\Models\Management;
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

    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $addresses = Address
            ::orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $addresses
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'name', 'like', $s );
                });
        }

        $addresses = $addresses
            ->paginate( 30 )
            ->appends( compact( 'search' ) );

        return view( 'catalog.addresses.index' )
            ->with( 'addresses', $addresses );

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

        return view( 'catalog.addresses.create' )
            ->with( 'managements', $managements );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate( $request, Address::getRules() );

        $address = Address::create( $request->all() );

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

        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найдена' ] );
        }

        $managements = Management
            ::orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $allowedManagements = $managements->toArray();

        $addressManagements = [];

        $res = AddressManagement
            ::where( 'address_id', '=', $address->id )
            ->with( 'management', 'type' )
            ->get();

        foreach ( $res as $r )
        {
            if ( !isset( $addressManagements[ $r->management_id ] ) )
            {
                $addressManagements[ $r->management_id ] = [ $r->management, new Collection() ];
                unset( $allowedManagements[ $r->management_id ] );
            }
            if ( $r->type_id )
            {
                $addressManagements[ $r->management_id ][ 1 ]->push( $r->type );
            }
        }

        foreach ( $addressManagements as $management_id => & $arr )
        {
            $allowedTypes = Type
                ::whereNotIn( 'id', $arr[1]->pluck( 'id' ) )
                ->get();
            $arr[2] = $allowedTypes;
        }

        return view( 'catalog.addresses.edit' )
            ->with( 'address', $address )
            ->with( 'managements', $managements )
            ->with( 'addressManagements', $addressManagements )
            ->with( 'allowedManagements', $allowedManagements );

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

        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найдена' ] );
        }

        $this->validate( $request, Address::getRules( $address->id ) );

        $address->fill( $request->all() );
        $address->save();

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

        $q = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $addresses = Address
            ::select(
                'id',
                'name AS text'
            )
            ->where( 'name', 'like', $q )
            ->orderBy( 'name' )
            ->get();

        return $addresses;

    }

    public function addManagements ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        $address->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнители успешно добавлены' );

    }

    public function addTypes ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        $management = Management::find( $request->get( 'management_id' ) );

        \DB::beginTransaction();

        foreach ( $request->get( 'types' ) as $type_id )
        {
            $addressManagement = AddressManagement
                ::create([
                    'address_id'        => $address->id,
                    'management_id'     => $management->id,
                    'type_id'           => $type_id
                ]);
            if ( $addressManagement instanceof MessageBag )
            {
                return redirect()->back()
                    ->withErrors( $addressManagement );
            }
        }

        \DB::commit();

        return redirect()->back()
            ->with( 'success', 'Типы успешно назначены' );

    }

}
