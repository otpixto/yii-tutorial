<?php

namespace App\Http\Controllers\Catalog;

use App\Models\AddressManagement;
use App\Models\Category;
use App\Models\Management;
use Illuminate\Http\Request;

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

        return view( 'catalog.managements.edit' )
            ->with( 'management', $management );

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

        $management->fill( $request->all() );
        $management->save();

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

}
