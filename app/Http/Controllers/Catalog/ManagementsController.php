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

class ManagementsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Управляющие организации' );
    }

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
                    'Адрес'                 => $management->address,
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
        Title::add( 'Добавить УО' );
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

        $rules = Management::$rules;
        //$rules['services'] = 'nullable|in:' . implode( ',', Management::$services );

        $this->validate( $request, $rules );

        $management = Management::create( $request->all() );

        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно добавлена' );

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

        Title::add( 'Редактировать УО' );

        $management = Management::find( $id );

        if ( !$management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $allowedAddresses = Address
            ::whereNotIn( 'id', $management->addresses->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $managementAddresses = $management->addresses()
            ->orderBy( 'name' )
            ->get();

        $allowedTypes = Type
            ::whereNotIn( 'id', $management->types->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $managementTypes = $management->types()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.managements.edit' )
            ->with( 'management', $management )
            ->with( 'allowedAddresses', $allowedAddresses )
            ->with( 'managementAddresses', $managementAddresses )
            ->with( 'allowedTypes', $allowedTypes )
            ->with( 'managementTypes', $managementTypes );

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
                ->withErrors( [ 'УО не найдена' ] );
        }

        $rules = Management::$rules;
        //$rules['services'] = 'nullable|in:' . implode( ',', Management::$services );

        $this->validate( $request, $rules );

        $management->edit( $request->all() );

        return redirect()->route( 'managements.edit', $management->id )
            ->with( 'success', 'УО успешно отредактирована' );

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

        $type_id = $request->get( 'type_id' );
        $address_id = $request->get( 'address_id' );

        $managements = Management
            ::whereHas( 'types', function ( $q ) use ( $type_id )
            {
                return $q
                    ->where( 'type_id', '=', $type_id );
            })
            ->whereHas( 'addresses', function ( $q ) use ( $address_id, $type_id )
            {
                return $q
                    ->where( 'address_id', '=', $address_id )
                    ->whereHas( 'types', function ( $q2 ) use ( $type_id )
                    {
                        return $q2
                            ->where( 'type_id', '=', $type_id );
                    });
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

    public function addAddresses ( Request $request )
    {

        $management = Management::find( $request->get( 'management_id' ) );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $management->addresses()->attach( $request->get( 'addresses', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно добавлены' );

    }

    public function delAddress ( Request $request )
    {

        $management = Management::find( $request->get( 'management_id' ) );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $management->addresses()->detach( $request->get( 'address_id' ) );

        return redirect()->back()
            ->with( 'success', 'Адрес успешно удален' );

    }

    public function addTypes ( Request $request )
    {

        $management = Management::find( $request->get( 'management_id' ) );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $management->types()->attach( $request->get( 'types', [] ) );

        return redirect()->back()
            ->with( 'success', 'Типы успешно назначены' );

    }

    public function delType ( Request $request )
    {

        $management = Management::find( $request->get( 'management_id' ) );
        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }
        $management->types()->detach( $request->get( 'type_id' ) );

        return redirect()->back()
            ->with( 'success', 'Тип успешно удален' );

    }

    public function telegram ( Request $request )
    {

        $management = Management::find( $request->get( 'id' ) );

        foreach ( $management->subscriptions as $subscription )
        {
            $subscription->delete();
        }

        switch ( $request->get( 'action' ) )
        {

            case 'gen':
            case 'on':

                $management->telegram_code = $this->genCode();

                break;

            case 'off':

                $management->telegram_code = null;

                break;

        }

        $management->save();

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

}
