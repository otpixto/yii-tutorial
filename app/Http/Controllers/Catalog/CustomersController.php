<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Management;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class CustomersController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Заявители' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $region = $request->get( 'region' );

        $customers = Customer
            ::mine()
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $customers
                ->where( function ( $q ) use ( $s, $search )
                {
                    return $q
                        ->where( 'firstname', 'like', $s )
                        ->orWhere( 'middlename', 'like', $s )
                        ->orWhere( 'lastname', 'like', $s )
                        ->orWhere( 'phone', '=', mb_substr( preg_replace( '/\D/', '', $search ), - 10 ) )
                        ->orWhere( 'phone2', '=', mb_substr( preg_replace( '/\D/', '', $search ), - 10 ) );
                });
        }

        if ( !empty( $region ) )
        {
            $customers
                ->where( 'region_id', '=', $region );
        }

        if ( \Input::get( 'export' ) == 1 )
        {
            $customers = $customers->get();
            $data = [];
            foreach ( $customers as $customer )
            {
                $data[] = [
                    'ФИО'                   => $customer->getName(),
                    'Телефон(ы)'            => $customer->getPhones(),
                    'Адрес проживания'      => $customer->getAddress(),
                    'E-mail'                => $customer->email,
                ];
            }
            \Excel::create( 'ЗАЯВИТЕЛИ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'ЗАЯВИТЕЛИ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        $customers = $customers
            ->paginate( 30 )
            ->appends( $request->all() );

        return view( 'catalog.customers.index' )
            ->with( 'customers', $customers )
            ->with( 'regions', $regions );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Title::add( 'Добавить заявителя' );
        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();
        return view( 'catalog.customers.create' )
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

        Title::add( 'Редактировать заявителя' );

        $customer = Customer::find( $id );

        if ( ! $customer )
        {
            return redirect()->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        $calls = $customer->calls( 30 );
        $tickets = $customer->tickets()->paginate( 30 );

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.customers.edit' )
            ->with( 'customer', $customer )
            ->with( 'tickets', $tickets )
            ->with( 'regions', $regions )
            ->with( 'calls', $calls );

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
		
		$res = $customer->edit( $request->all() );
		if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

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
        $region_id = $request->get( 'region_id', Region::getCurrent() ? Region::$current_region->id : null );

        $customers = Customer
            ::where( function ( $q ) use ( $phone )
            {
                return $q
                    ->where( 'phone', '=', $phone )
                    ->orWhere( 'phone2', '=', $phone );
            })
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' );

        if ( $region_id )
        {
            $customers
                ->where( 'region_id', '=', $region_id );
        }

        $customers = $customers
            ->get();

        if ( $customers->count() )
        {
            return view( 'catalog.customers.select' )
                ->with( 'customers', $customers );
        }

    }

}
