<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Provider;
use App\Models\Segment;
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

        if ( $request->ajax() )
        {

            $customers = Customer
                ::mine()
                ->orderBy( Customer::$_table . '.lastname' )
                ->orderBy( Customer::$_table . '.firstname' )
                ->orderBy( Customer::$_table . '.middlename' );

            if ( ! empty( $request->get( 'lastname' ) ) )
            {
                $s = '%' . str_replace( ' ', '%', $request->get( 'lastname' ) ) . '%';
                $customers
                    ->where( Customer::$_table . '.lastname', 'like', $s );
            }

            if ( ! empty( $request->get( 'firstname' ) ) )
            {
                $s = '%' . str_replace( ' ', '%', $request->get( 'firstname' ) ) . '%';
                $customers
                    ->where( Customer::$_table . '.firstname', 'like', $s );
            }

            if ( ! empty( $request->get( 'middlename' ) ) )
            {
                $s = '%' . str_replace( ' ', '%', $request->get( 'middlename' ) ) . '%';
                $customers
                    ->where( Customer::$_table . '.middlename', 'like', $s );
            }

            if ( ! empty( $request->get( 'provider_id' ) ) )
            {
                $customers
                    ->where( Customer::$_table . '.provider_id', '=', $request->get( 'provider_id' ) );
            }

            if ( ! empty( $request->get( 'actual_building_id' ) ) )
            {
                $customers
                    ->where( Customer::$_table . '.actual_building_id', '=', $request->get( 'actual_building_id' ) );
            }

            if ( ! empty( $request->get( 'actual_flat' ) ) )
            {
                $customers
                    ->where( Customer::$_table . '.actual_flat', '=', $request->get( 'actual_flat' ) );
            }

            if ( ! empty( $request->get( 'phone' ) ) )
            {
                $p = str_replace( '+7', '', $request->get( 'phone' ) );
                $p = preg_replace( '/[^0-9_]/', '', $p );
                $p = '%' . mb_substr( $p, - 10 ) . '%';
                $customers
                    ->where( function ( $q ) use ( $p )
                    {
                        return $q
                            ->where( Customer::$_table . '.phone', 'like', $p )
                            ->orWhere( Customer::$_table . '.phone2', 'like', $p );
                    });
            }

            if ( ! empty( $request->get( 'tags' ) ) )
            {
                $_tags = explode( ',', $request->get( 'tags' ) );
                $customers
                    ->whereHas( 'tags', function ( $tags ) use ( $_tags )
                    {
                        $i = 0;
                        foreach ( $_tags as $tag )
                        {
                            $tag = trim( $tag );
                            if ( empty( $tag ) ) continue;
                            if ( $i ++ == 0 )
                            {
                                $tags->where( 'text', '=', $tag );
                            }
                            else
                            {
                                $tags->orWhere( 'text', '=', $tag );
                            }
                        }
                        return $tags;
                    });
            }

            $customers = $customers
                ->with(
                    'actualBuilding',
                    'user'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            $this->addLog( 'Просмотрел список заявителей (стр.' . $request->get( 'page', 1 ) . ')' );

            return view( 'catalog.customers.parts.list' )
                ->with( 'customers', $customers );

        }

        return view( 'catalog.customers.index' )
            ->with( 'request', $request );

        /*if ( \Input::get( 'export' ) == 1 )
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
        }*/

    }

    public function searchForm ( Request $request )
    {

        if ( ! \Auth::user()->can( 'catalog.customers.search' ) )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Доступ запрещен' );
        }

        if ( ! empty( $request->get( 'segment_id' ) ) )
        {
            $segment = Segment::find( $request->get( 'segment_id' ) );
        }

        if ( ! empty( $request->get( 'actual_building_id' ) ) )
        {
            $actual_building = Building::where( 'id', $request->get( 'actual_building_id' ) )->pluck( 'name', 'id' );
        }

        return view( 'catalog.customers.parts.search' )
            ->with( 'segment', $segment ?? [] )
            ->with( 'actual_building', $actual_building ?? [] );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить заявителя' );
        return view( 'catalog.customers.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $rules = [
            'firstname'             => 'required|max:191',
            'middlename'            => 'nullable|max:191',
            'lastname'              => 'required|max:191',
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'actual_building_id'    => 'nullable|integer',
            'actual_flat'           => 'nullable|string',
            'email'                 => 'nullable|email',
        ];

        $this->validate( $request, $rules );

        $customer = Customer::create( $request->all() );
        if ( $customer instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $customer );
        }
        $customer->save();

        self::clearCache();

        return redirect()->route( 'customers.index' )
            ->with( 'success', 'Заявитель успешно добавлен' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        return redirect()->route( 'customers.index' );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        Title::add( 'Редактировать заявителя' );

        $customer = Customer::find( $id );

        if ( ! $customer )
        {
            return redirect()->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        $calls = $customer->calls( 30 );
        $tickets = $customer
            ->tickets()
            ->orderByDesc('id')
            ->whereHas( 'type' )
            ->paginate( 20 );

        return view( 'catalog.customers.edit' )
            ->with( 'customer', $customer )
            ->with( 'tickets', $tickets )
            ->with( 'calls', $calls );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {

        $rules = [
            'firstname'             => 'required|max:191',
            'middlename'            => 'nullable|max:191',
            'lastname'              => 'required|max:191',
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'actual_building_id'    => 'nullable|integer',
            'actual_flat'           => 'nullable|string',
            'email'                 => 'nullable|email',
        ];

        $this->validate( $request, $rules );

        $customer = Customer::find( $id );

        if ( ! $customer )
        {
            return redirect()->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }
		
		$res = $customer->edit( $request->all() );
		if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'customers.edit', $customer->id )
            ->with( 'success', 'Заявитель успешно отредактирован' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {
        //
    }

    public function search ( Request $request )
    {

        $param = $request->get( 'param' );
        $value = trim( $request->get( 'value', '' ) );
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        switch ( $param )
        {
            case 'phone':
            case 'phone2':
                $value = str_replace( '+7', '', $value );
                $value = mb_substr( preg_replace( '/[^0-9]/', '', $value ), -10 );
                $union = Customer
                    ::mine()
                    ->select( 'phone2' . ' as label' )
                    ->where( 'phone2', 'like', $value . '%' );
                $customers = Customer
                    ::mine()
                    ->select( 'phone' . ' as label' )
                    ->where( 'phone', 'like', $value . '%' )
                    ->union( $union );
                break;
            case 'lastname':
            case 'middlename':
            case 'firstname':
                $customers = Customer
                    ::mine()
                    ->select( $param . ' as label' )
                    ->where( $param, 'like', $value . '%' );
                break;
            case 'phone_by_name':
                $firstname = trim( $request->get( 'firstname', '' ) );
                $middlename = trim( $request->get( 'middlename', '' ) );
                $lastname = trim( $request->get( 'lastname', '' ) );
                $customers = Customer
                    ::mine()
                    ->name( $firstname, $middlename, $lastname )
                    ->select(
                        'phone',
                        'actual_building_id',
                        'actual_flat'
                    );
                if ( $provider_id )
                {
                    $customers
                        ->where( 'provider_id', '=', $provider_id );
                }
                if ( $customers->count() == 1 )
                {
                    $customer = $customers->first();
                    $customer->actualBuilding;
                }
                else
                {
                    $customer = [];
                }
                return $customer;
                break;
            case 'name_by_phone':
                $value = str_replace( '+7', '', $request->get( 'phone', '' ) );
                $value = mb_substr( preg_replace( '/[^0-9]/', '', $value ), -10 );
                $customer = Customer
                    ::mine()
                    ->where( 'phone', '=', $value )
                    ->select(
                        'firstname',
                        'middlename',
                        'lastname',
                        'actual_building_id',
                        'actual_flat'
                    );
                if ( $provider_id )
                {
                    $customer
                        ->where( 'provider_id', '=', $provider_id );
                }
                $customer = $customer->first();
                if ( $customer )
                {
                    $customer->actualBuilding;
                }
                return $customer;
                break;
            default:
                return [];
                break;
        }

        if ( ! empty( $provider_id ) )
        {
            $customers
                ->where( 'provider_id', '=', $provider_id );
        }

        $customers = $customers
            ->distinct( 'label' )
            ->get();

        return $customers;

    }

}
