<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Address;
use App\Models\BaseModel;
use App\Models\Management;
use App\Models\ManagementSubscription;
use App\Models\Region;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ManagementsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Управляющие организации' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $region = $request->get( 'region' );
        $category = $request->get( 'category' );
        $address = $request->get( 'address' );
        $type = $request->get( 'type' );

        $managements = Management
            ::mine()
            ->orderBy( Management::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $managements
                ->where( function ( $q ) use ( $search )
                {
                    $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
                    $p = mb_substr( preg_replace( '/\D/', '', $search ), - 10 );
                    $q
                        ->where( Management::$_table . '.name', 'like', $s )
                        ->orWhere( Management::$_table . '.guid', 'like', $s )
                        ->orWhereHas( 'address', function ( $q2 ) use ( $s )
                        {
                            return $q2
                                ->where( Address::$_table . '.name', 'like', $s );
                        });
                    if ( ! empty( $p ) )
                    {
                        $q
                            ->orWhere( Management::$_table . '.phone', '=', $p )
                            ->orWhere( Management::$_table . '.phone2', '=', $p );
                    }
                });
        }

        if ( ! empty( $category ) )
        {
            $managements
                ->category( $category );
        }

        if ( ! empty( $region ) )
        {
            $managements
                ->whereHas( 'regions', function ( $regions ) use ( $region )
                {
                    return $regions
                        ->where( Region::$_table . '.id', '=', $region );
                });
        }

        if ( ! empty( $address ) )
        {
            $managements
                ->whereHas( 'addresses', function ( $q ) use ( $address )
                {
                    return $q
                        ->where( Address::$_table . '.id', '=', $address );
                });
        }

        if ( ! empty( $type ) )
        {
            $managements
                ->whereHas( 'types', function ( $q ) use ( $type )
                {
                    return $q
                        ->where( Type::$_table . '.id', '=', $type );
                });
        }

        if ( \Input::get( 'export' ) == 1 )
        {
            $managements = $managements->get();
            $data = [];
            foreach ( $managements as $management )
            {
                $data[] = [
                    'Категория'             => $management->getCategory(),
                    'Услуги'                => $management->services,
                    'Наименование'          => $management->name,
                    'Телефон(ы)'            => $management->getPhones(),
                    'Адрес'                 => $management->address->name ?? '',
                    'График работы'         => $management->schedule,
                    'ФИО руководителя'      => $management->director,
                    'E-mail'                => $management->email,
                    'Сайт'                  => $management->site,
                ];
            }
            \Excel::create( 'ЭКСПЛУАТИРУЮЩИЕ ОРГАНИЗАЦИИ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'ЭКСПЛУАТИРУЮЩИЕ ОРГАНИЗАЦИИ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $managements = $managements
            ->paginate( 30 )
            ->appends( $request->all() );

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.managements.index' )
            ->with( 'managements', $managements )
            ->with( 'regions', $regions );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить УО' );
        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();
        return view( 'catalog.managements.create' )
            ->with( 'regions', $regions );
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
            'guid'                  => 'nullable|unique:managements,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'email'                 => 'nullable|email',
            'site'                  => 'nullable|url',
        ];

        $this->validate( $request, $rules );

        $management = Management::create( $request->all() );
        if ( $management instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $management );
        }
        $management->save();

        $management->regions()->attach( $request->get( 'region_id' ) );

        self::clearCache();

        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно добавлена' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        Title::add( 'Редактировать УО' );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementAddressesCount = $management->addresses()
            ->mine()
            ->count();

        $managementTypesCount = $management->types()
            ->count();

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.managements.edit' )
            ->with( 'management', $management )
            ->with( 'managementAddressesCount', $managementAddressesCount )
            ->with( 'managementTypesCount', $managementTypesCount )
            ->with( 'regions', $regions );

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

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $rules = [
            'guid'                  => 'nullable|unique:managements,guid,' . $management->id . '|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'email'                 => 'nullable|email',
            'site'                  => 'nullable|url',
        ];

        $this->validate( $request, $rules );

        $res = $management->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно отредактирована' );

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

        $type_id = $request->get( 'type_id' );
        $address_id = $request->get( 'address_id' );

        $managements = Management
			::mine()
            ->whereHas( 'types', function ( $types ) use ( $type_id )
            {
                return $types
                    ->where( Type::$_table . '.id', '=', $type_id );
            })
            ->whereHas( 'addresses', function ( $addresses ) use ( $address_id )
            {
                return $addresses
                    ->where( Address::$_table . '.id', '=', $address_id );
            })
            ->get();

        if ( ! $managements->count() )
        {
            return view( 'parts.error' )
                ->with( 'error', 'УО не найдены по заданным критериям' );
        }

        if ( ! empty( $request->get( 'selected' ) ) )
        {
            $selected = explode( ',', $request->get( 'selected' ) );
        }
        else
        {
            $selected = null;
        }

        return view( 'catalog.managements.select' )
            ->with( 'managements', $managements )
            ->with( 'selected', $selected );

    }

    public function executors ( Request $request )
    {
        $management = Management::find( $request->get( 'management_id' ) );
        if ( ! $management )
        {
            return false;
        }
        return $management->executors;
    }

    public function telegramOn ( Request $request, $id )
    {
        $management = Management::find( $id );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $management->telegram_code = $this->genCode();
        $management->save();
        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно отредактирована' );
    }

    public function telegramOff ( Request $request, $id )
    {
        $management = Management::find( $id );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        foreach ( $management->subscriptions as $subscription )
        {
            if ( $subscription->sendTelegram( 'Ваша подписка на <b>' . $subscription->management->name . '</b> прекращена' ) )
            {
                $subscription->addLog( 'Подписка прекращена' );
                $subscription->delete();
            }
        }
        $management->telegram_code = null;
        $management->save();
        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно отредактирована' );
    }

    public function telegramUnsubscribe ( Request $request, $id )
    {
        $management = Management::find( $id );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $subscription = $management->subscriptions()->find( $request->get( 'id' ) );
        if ( ! $subscription )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'Подписка не найдена' ] );
        }
        if ( $subscription->sendTelegram( 'Ваша подписка на <b>' . $subscription->management->name . '</b> прекращена' ) )
        {
            $subscription->addLog( 'Подписка прекращена' );
            $subscription->delete();
        }
        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно отредактирована' );
    }

    public function genCode ( $length = 4 )
    {
        $code = '';
        for ( $i = 0; $i < $length; $i ++ )
        {
            $code .= rand( 0, 9 );
        }
        return $code;
    }

    public function addresses ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementAddresses = $management->addresses()
            ->mine()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view( 'catalog.managements.addresses' )
            ->with( 'management', $management )
            ->with( 'managementAddresses', $managementAddresses );

    }

    public function addressesSearch ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $addresses = Address
            ::mine( Address::IGNORE_REGION )
            ->select(
                Address::$_table . '.id',
                Address::$_table . '.name AS text'
            )
            ->where( Address::$_table . '.name', 'like', $s )
            ->whereNotIn( Address::$_table . '.id', $management->addresses()->pluck( Address::$_table . '.id' ) )
            ->orderBy( Address::$_table . '.name' )
            ->get();

        return $addresses;

    }

    public function addressesAdd ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->addresses()->attach( $request->get( 'addresses', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно назначены' );

    }

    public function addressesDel ( Request $request, $id )
    {

        $rules = [
            'address_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->addresses()->detach( $request->get( 'address_id' ) );

    }

    public function types ( Request $request, $id )
    {

        Title::add( 'Привязка Классификатора' );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementTypes = $management->types()
            ->orderBy( Type::$_table . '.name' )
            ->paginate( 30 );

        $allowedTypes = Type
            ::whereNotIn( Type::$_table . '.id', $management->types()->pluck( Type::$_table . '.id' ) )
            ->orderBy( Type::$_table . '.name' )
            ->pluck( Type::$_table . '.name', Type::$_table . '.id' );

        return view( 'catalog.managements.types' )
            ->with( 'management', $management )
            ->with( 'managementTypes', $managementTypes )
            ->with( 'allowedTypes', $allowedTypes );

    }

    public function typesAdd ( Request $request, $id )
    {

        $management = Management::find( $id );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->types()->attach( $request->get( 'types', [] ) );

        return redirect()->back()
            ->with( 'success', 'Типы успешно назначены' );

    }

    public function typesDel ( Request $request, $id )
    {

        $rules = [
            'type_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->types()->detach( $request->get( 'type_id' ) );

    }

}
