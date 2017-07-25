<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Address;
use App\Models\AddressManagement;
use App\Models\Category;
use App\Models\Management;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ManagementsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $managements = Management
            ::orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $managements
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'name', 'like', $s )
                        ->orWhere( 'address', 'like', $s )
                        ->orWhere( 'phone', 'like', $s );
                });
        }

        $managements = $managements->paginate( 30 );

        return view( 'catalog.managements.index' )
            ->with( 'managements', $managements );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view( 'catalog.managements.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate( $request, Management::$rules );

        $management = Management::create( $request->all() );

        return redirect()->route( 'managements.index' )
            ->with( 'success', 'УК успешно добавлена' );

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

        $management = Management::find( $id );

        if ( !$management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УК не найдена' ] );
        }

        $addresses = Address
            ::orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $allowedAddresses = $addresses->toArray();

        $addressManagements = [];

        $res = AddressManagement
            ::where( 'management_id', '=', $management->id )
            ->with( 'address', 'type' )
            ->get();

        foreach ( $res as $r )
        {
            if ( !isset( $addressManagements[ $r->address_id ] ) )
            {
                $addressManagements[ $r->address_id ] = [ $r->address, new Collection() ];
                unset( $allowedAddresses[ $r->address_id ] );
            }
            if ( $r->type_id )
            {
                $addressManagements[ $r->address_id ][ 1 ]->push( $r->type );
            }
        }

        foreach ( $addressManagements as $address_id => & $arr )
        {
            $allowedTypes = Type
                ::whereNotIn( 'id', $arr[1]->pluck( 'id' ) )
                ->get();
            $arr[2] = $allowedTypes;
        }

        return view( 'catalog.managements.edit' )
            ->with( 'management', $management )
            ->with( 'addressManagements', $addressManagements )
            ->with( 'allowedAddresses', $allowedAddresses );

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

        $management = Management::find( $id );

        if ( !$management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УК не найдена' ] );
        }

        $this->validate( $request, Management::$rules );

        $management->edit( $request->all() );

        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УК успешно отредактирована' );

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

        $res = AddressManagement
            ::select( 'management_id' )
            ->where( 'type_id', '=', $request->get( 'type_id' ) )
            ->where( 'address_id', '=', $request->get( 'address_id' ) )
            ->get();

        if ( ! $res->count() )
        {
            return view( 'parts.error' )
                ->with( 'error', 'УК не найдены по заданным критериям' );
        }

        $managements = Management
            ::whereIn( 'id', $res->pluck( 'management_id' ) )
            ->get();

        return view( 'catalog.managements.select' )
            ->with( 'managements', $managements );

    }

    public function addAddresses ( Request $request )
    {

        $management = Management::find( $request->get( 'management_id' ) );
        $management->addresses()->attach( $request->get( 'addresses' ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно добавлены' );

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
