<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Operator\Category;
use App\Models\Operator\Customer;
use App\Models\Operator\Management;
use Illuminate\Http\Request;

class CustomersController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $customers = Customer
            ::orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $customers
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'firstname', 'like', $s )
                        ->orWhere( 'middlename', 'like', $s )
                        ->orWhere( 'lastname', 'like', $s )
                        ->orWhere( 'phone', 'like', $s )
                        ->orWhere( 'phone2', 'like', $s );
                });
        }

        $customers = $customers->paginate( 30 );

        return view( 'catalog.customers.index' )
            ->with( 'customers', $customers );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view( 'catalog.customers.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate( $request, Customer::$rules );

        $customer = Customer::create( $request->all() );

        return redirect()->route( 'customers.index' )
            ->with( 'success', 'Заявитель успешно добавлен' );

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

        $customer = Customer::find( $id );

        if ( !$customer )
        {
            return redirect()->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        return view( 'catalog.customers.edit' )
            ->with( 'customer', $customer );

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

        $customer = Customer::find( $id );

        if ( !$customer )
        {
            return redirect()->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        $this->validate( $request, Customer::$rules );

        $customer->fill( $request->all() );
        $customer->save();

        return redirect()->route( 'customers.edit', $customer->id )
            ->with( 'success', 'Заявитель успешно отредактирован' );

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

        $phone = mb_substr( preg_replace( '/[^0-9]/', '', $request->get( 'phone' ) ), -10 );

        $customers = Customer
            ::where( 'phone', '=', $phone )
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        foreach ( $customers as $customer )
        {
            $customer->full_name = $customer->getName();
        }

        return $customers;

    }

}
