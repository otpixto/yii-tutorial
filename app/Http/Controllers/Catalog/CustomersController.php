<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use Carbon\Carbon;
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

            $customers = Customer::search( $request );

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


    }

    public function export ( Request $request )
    {

        $customers = Customer::search( $request );

        $addressCustomers = [];

        $addressCustomers[ 'г. Жуковский' ] = clone $customers;

        $addressCustomers[ 'г. Жуковский' ] = $addressCustomers[ 'г. Жуковский' ]
            ->whereHas( 'actualBuilding', function ( $q )
            {
                return $q->where( Building::$_table . '.name', 'like', '%г. Жуковский%' );
            } )
            ->get();

        $addressCustomers[ 'г. Раменское' ] = clone $customers;

        $addressCustomers[ 'г. Раменское' ] = $addressCustomers[ 'г. Раменское' ]
            ->whereHas( 'actualBuilding', function ( $q )
            {
                return $q->where( Building::$_table . '.name', 'like', '%г. Раменское%' );
            } )
            ->get();

        $addressCustomers[ 'Раменский' ] = clone $customers;

        $addressCustomers[ 'Раменский' ] = $addressCustomers[ 'Раменский' ]
            ->whereHas( 'actualBuilding', function ( $q )
            {
                return $q
                    ->where( Building::$_table . '.name', 'like', '%Раменский%' )
                    ->where( Building::$_table . '.name', 'not like', '%г. Раменское%' );
            } )
            ->get();

        $export = \Excel::create( 'Заявители', function ( $excel ) use ( $addressCustomers )
        {
            foreach ( $addressCustomers as $addressName => $addressCustomerCollection )
            {
                $data = [];
                $i = 0;
                foreach ( $addressCustomerCollection as $addressCustomer )
                {
                    $data[ $i ] = [
                        'Адрес проживания' => $addressCustomer->actualBuilding->name,
                        'Квартира' => $addressCustomer->actual_flat,
                        'Фамилия' => $addressCustomer->lastname,
                        'Имя' => $addressCustomer->firstname,
                        'Отчество' => $addressCustomer->middlename,
                        'Телефон' => $addressCustomer->phone,
                        'Доп. Телефон' => $addressCustomer->phone2,
                        'E-mail' => $addressCustomer->email,
                        'Доступ в ЛК (есть / нет)' => ( isset( $addressCustomer->user ) && $addressCustomer->user->isActive() ) ? 'Есть' : 'Нет',
                        'Номер заявления' => (isset($addressCustomer->tickets[0])) ? $addressCustomer->tickets[0]->vendor_number : '',
                        'Дата заявления' => (isset($addressCustomer->tickets[0]) && !empty($addressCustomer->tickets[0]->vendor_date))
                            ? Carbon::parse($addressCustomer->tickets[0]->vendor_date)->format('d.m.Y H:i') : '',
                        'Теги' => '',
                    ];
                    $i ++;
                }

                $excel->sheet( 'Заявители ' . $addressName, function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                } );

            }
        } );

        $export->download( 'xls' );

        $this->addLog( 'Выгрузил список зданий' );

        die;
    }

    public function searchForm ( Request $request )
    {

        if ( ! \Auth::user()
            ->can( 'catalog.customers.search' ) )
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
            $actual_building = Building::where( 'id', $request->get( 'actual_building_id' ) )
                ->pluck( 'name', 'id' );
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $rules = [
            'firstname' => 'required|max:191',
            'middlename' => 'nullable|max:191',
            'lastname' => 'required|max:191',
            'phone' => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2' => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'actual_building_id' => 'nullable|integer',
            'actual_flat' => 'nullable|string',
            'email' => 'nullable|email',
        ];

        $this->validate( $request, $rules );

        $customer = Customer::create( $request->all() );
        if ( $customer instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $customer );
        }
        $customer->save();

        self::clearCache();

        return redirect()
            ->route( 'customers.index' )
            ->with( 'success', 'Заявитель успешно добавлен' );

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        return redirect()->route( 'customers.index' );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        Title::add( 'Редактировать заявителя' );

        $customer = Customer::find( $id );

        if ( ! $customer )
        {
            return redirect()
                ->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        $calls = $customer->calls( 30 );
        $tickets = $customer
            ->tickets()
            ->orderByDesc( 'id' )
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
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {

        $rules = [
            'firstname' => 'required|max:191',
            'middlename' => 'nullable|max:191',
            'lastname' => 'required|max:191',
            'phone' => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2' => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'actual_building_id' => 'nullable|integer',
            'actual_flat' => 'nullable|string',
            'email' => 'nullable|email',
        ];

        $this->validate( $request, $rules );

        $customer = Customer::find( $id );

        if ( ! $customer )
        {
            return redirect()
                ->route( 'customers.index' )
                ->withErrors( [ 'Заявитель не найден' ] );
        }

        $res = $customer->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()
            ->route( 'customers.edit', $customer->id )
            ->with( 'success', 'Заявитель успешно отредактирован' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
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
                $value = mb_substr( preg_replace( '/[^0-9]/', '', $value ), - 10 );
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
                } else
                {
                    $customer = [];
                }
                return $customer;
                break;
            case 'name_by_phone':
                $value = str_replace( '+7', '', $request->get( 'phone', '' ) );
                $value = mb_substr( preg_replace( '/[^0-9]/', '', $value ), - 10 );
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
