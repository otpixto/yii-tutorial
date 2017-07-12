<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Operator\Address;
use App\Models\Operator\Management;
use Illuminate\Http\Request;

class AddressesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );
        $management = trim( \Input::get( 'management', '' ) );

        $addresses = Address
            ::select(
                'addresses.*',
                'managements.name AS management_name'
            )
            ->join( 'managements', 'managements.id', '=', 'addresses.management_id' )
            ->orderBy( 'addresses.name' );

        if ( !empty( $management ) )
        {
            $addresses
                ->where( 'addresses.management_id', '=', $management );
        }

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $addresses
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'addresses.name', 'like', $s )
                        ->orWhere( 'managements.name', 'like', $s );
                });
        }

        $addresses = $addresses
            ->paginate( 30 )
            ->appends( compact( 'search', 'management' ) );

        $managements = Management::orderBy( 'name' )->get();

        return view( 'catalog.addresses.index' )
            ->with( 'addresses', $addresses )
            ->with( 'managements', $managements );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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

        $address = Address::find( $id );

        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найдена' ] );
        }

        $managements = Management::orderBy( 'name' )->pluck( 'name', 'id' );

        return view( 'catalog.addresses.edit' )
            ->with( 'address', $address )
            ->with( 'managements', $managements );

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
                'addresses.*',
                'managements.name AS management_name',
                'managements.address AS management_address',
                'managements.phone AS management_phone'
            )
            ->join( 'managements', 'managements.id', '=', 'addresses.management_id' )
            ->where( 'addresses.name', 'like', $q )
            ->orderBy( 'addresses.name' )
            ->get();

        return $addresses;

    }

}
