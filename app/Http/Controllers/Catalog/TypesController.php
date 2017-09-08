<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Category;
use App\Models\Management;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class TypesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Классификатор' );
    }

    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );
        $category = trim( \Input::get( 'category', '' ) );

        $types = Type
            ::select(
                'types.*',
                'categories.name AS category_name'
            )
            ->join( 'categories', 'categories.id', '=', 'types.category_id' )
            ->orderBy( 'categories.name' )
            ->orderBy( 'types.name' );

        if ( !empty( $category ) )
        {
            $types
                ->where( 'types.category_id', '=', $category );
        }

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $types
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'types.name', 'like', $s )
                        ->orWhere( 'categories.name', 'like', $s );
                });
        }

        $types = $types->paginate( 30 );

        $categories = Category::orderBy( 'name' )->get();

        return view( 'catalog.types.index' )
            ->with( 'types', $types )
            ->with( 'categories', $categories );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        Title::add( 'Добавить Тип обращений' );

        $categories = Category::orderBy( 'name' )->pluck( 'name', 'id' );

        return view( 'catalog.types.create' )
            ->with( 'categories', $categories );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate( $request, Type::getRules() );

        $type = Type::create( $request->all() );

        return redirect()->route( 'types.index' )
            ->with( 'success', 'Тип успешно добавлен' );

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

        Title::add( 'Редактировать Тип обращений' );

        $type = Type::find( $id );

        if ( !$type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найдена' ] );
        }

        $allowedAddresses = Address
            ::whereNotIn( 'id', $type->addresses->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $typeAddresses = $type->addresses()
            ->orderBy( 'name' )
            ->get();

        $allowedManagements = Management
            ::whereNotIn( 'id', $type->managements->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $typeManagements = $type->managements()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.types.edit' )
            ->with( 'type', $type )
            ->with( 'categories', Category::orderBy( 'name' )->pluck( 'name', 'id' ) )
            ->with( 'allowedAddresses', $allowedAddresses )
            ->with( 'typeAddresses', $typeAddresses )
            ->with( 'allowedManagements', $allowedManagements )
            ->with( 'typeManagements', $typeManagements );

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

        $type = Type::find( $id );

        if ( !$type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найдена' ] );
        }

        $this->validate( $request, Type::getRules( $type->id ) );

        $res = $type->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( $res );
        }

        return redirect()->route( 'types.edit', $type->id )
            ->with( 'success', 'Тип успешно отредактирован' );

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

        $type = Type
            ::where( 'id', $request->get( 'type_id' ) )
            ->first();

        $type->category_name = $type->category->name;

        return $type;

    }

    public function addManagements ( Request $request )
    {

        $type = Type::find( $request->get( 'type_id' ) );
        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найден' ] );
        }
        $type->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнители успешно добавлены' );

    }

    public function delManagement ( Request $request )
    {

        $type = Type::find( $request->get( 'type_id' ) );
        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найден' ] );
        }
        $type->managements()->detach( $request->get( 'management_id' ) );

        return redirect()->back()
            ->with( 'success', 'Исполнитель успешно удален' );

    }

    public function addAddresses ( Request $request )
    {

        $type = Type::find( $request->get( 'type_id' ) );
        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найден' ] );
        }
        $type->addresses()->attach( $request->get( 'addresses', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно назначены' );

    }

    public function delAddress ( Request $request )
    {

        $type = Type::find( $request->get( 'type_id' ) );
        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найден' ] );
        }
        $type->addresses()->detach( $request->get( 'address_id' ) );

        return redirect()->back()
            ->with( 'success', 'Адрес успешно удален' );

    }

}
