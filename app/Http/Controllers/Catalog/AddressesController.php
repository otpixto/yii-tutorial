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
                ->where( 'name', 'like', $s );
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

        $this->validate( $request, Address::$rules );

        $address = Address::create( $request->all() );
        if ( $address instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $address );
        }
        $address->save();

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

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $allowedManagements = Management
            ::whereNotIn( 'id', $address->managements->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $addressManagements = $address->managements()
            ->orderBy( 'name' )
            ->get();

        $allowedTypes = Type
            ::whereNotIn( 'id', $address->types->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $addressTypes = $address->types()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.addresses.edit' )
            ->with( 'address', $address )
            ->with( 'addressManagements', $addressManagements )
            ->with( 'allowedManagements', $allowedManagements )
            ->with( 'allowedTypes', $allowedTypes )
            ->with( 'addressTypes', $addressTypes );

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
        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $this->validate( $request, Address::$rules );

        $res = $address->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

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
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнители успешно добавлены' );

    }

    public function delManagement ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->managements()->detach( $request->get( 'management_id' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнитель успешно удален' );

    }

    public function addTypes ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->types()->attach( $request->get( 'types', [] ) );

        return redirect()->back()
            ->with( 'success', 'Типы успешно назначены' );

    }

    public function delType ( Request $request )
    {

        $address = Address::find( $request->get( 'address_id' ) );
        if ( !$address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
        $address->types()->detach( $request->get( 'type_id' ) );

        return redirect()->back()
            ->with( 'success', 'Тип успешно удален' );

    }

}
