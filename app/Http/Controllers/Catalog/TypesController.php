<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Category;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
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

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $category_id = trim( $request->get( 'category_id', '' ) );
        $building_id = trim( $request->get( 'building_id', '' ) );
        $management_id = trim( $request->get( 'management_id', '' ) );
        $provider_id = trim( $request->get( 'provider_id', '' ) );

        $types = Type
            ::mine()
            ->select(
                Type::$_table . '.*',
                Category::$_table . '.name AS category_name'
            )
            ->join( 'categories', 'categories.id', '=', 'types.category_id' )
            ->orderBy( Category::$_table . '.name' )
            ->orderBy( Type::$_table .'.name' );

        if ( ! empty( $category_id ) )
        {
            $types
                ->where( Type::$_table . '.category_id', '=', $category_id );
        }

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $types
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( Type::$_table . '.name', 'like', $s )
                        ->orWhere( Category::$_table . '.name', 'like', $s )
                        ->orWhere( Type::$_table . '.guid', 'like', $s );
                });
        }

        if ( ! empty( $building_id ) )
        {
            $types
                ->whereHas( 'buildings', function ( $buildings ) use ( $building_id )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $building_id );
                });
        }

        if ( ! empty( $management_id ) )
        {
            $types
                ->whereHas( 'managements', function ( $managements ) use ( $management_id )
                {
                    return $managements
                        ->where( Management::$_table . '.id', '=', $management_id );
                });
        }

        if ( ! empty( $provider_id ) )
        {
            $types
                ->where( Type::$_table . '.provider_id', '=', $provider_id );
        }

        $types = $types
            ->with(
                'category'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $categories = Category
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        $log = Log::create([
            'text' => 'Просмотрел классификатор (стр.' . $request->get( 'page', 1 ) . ')'
        ]);
        $log->save();

        return view( 'catalog.types.index' )
            ->with( 'types', $types )
            ->with( 'categories', $categories )
            ->with( 'providers', $providers );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить Классификатор' );

        $categories = Category::orderBy( Category::$_table . '.name' )->pluck( Category::$_table . '.name', Category::$_table . '.id' );

        return view( 'catalog.types.create' )
            ->with( 'categories', $categories );
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
            'guid'                  => 'nullable|unique:types,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|unique:types,name|string|max:255',
            'category_id'           => 'required|integer',
            'period_acceptance'     => 'numeric',
            'period_execution'      => 'numeric',
            'need_act'              => 'boolean',
        ];

        $this->validate( $request, $rules );

        $type = Type::create( $request->all() );
        if ( $type instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $type );
        }
        $type->save();

        self::clearCache();

        return redirect()->route( 'types.edit', $type->id )
            ->with( 'success', 'Классификатор успешно добавлен' );

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

        Title::add( 'Редактировать Классификатор' );

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $parents = Type
            ::mine()
            ->whereNull( 'parent_id' )
            ->where( 'id', '!=', $type->id )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'catalog.types.edit' )
            ->with( 'type', $type )
            ->with( 'parents', $parents )
            ->with( 'categories', Category::orderBy( Category::$_table . '.name' )->pluck( Category::$_table . '.name', Category::$_table . '.id' ) );
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

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $rules = [
            'guid'                  => 'nullable|unique:types,guid,' . $type->id . '|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required_with:category_id|unique:types,name,' . $type->id . '|string|max:255',
            'category_id'           => 'required_with:name|integer',
            'period_acceptance'     => 'numeric',
            'period_execution'      => 'numeric',
            'price'                 => 'nullable|numeric',
            'need_act'              => 'boolean',
            'is_pay'                => 'boolean',
            'emergency'             => 'boolean',
        ];

        $this->validate( $request, $rules );

        $res = $type->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'types.edit', $type->id )
            ->with( 'success', 'Классификатор успешно отредактирован' );

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

        $type = Type
            ::where( 'id', $request->get( 'type_id' ) )
            ->first();

        $type->category_name = $type->category->name;

        return $type;

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка УО' );

        $type = Type::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $typeManagements = $type->managements()
            ->orderBy( Management::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $typeManagements
                ->where( Management::$_table . '.name', 'like', $s );
        }

        $typeManagements = $typeManagements
            ->paginate( 30 )
            ->appends( $request->all() );

        $availableManagements = Management
            ::mine()
            ->whereNotIn( Management::$_table . '.id', $type->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? 'Без родителя' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'catalog.types.managements' )
            ->with( 'type', $type )
            ->with( 'search', $search )
            ->with( 'typeManagements', $typeManagements )
            ->with( 'availableManagements', $availableManagements );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $res = Management
            ::mine()
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $type->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $managements = [];
        foreach ( $res as $r )
        {
            $name = $r->name;
            if ( $r->parent )
            {
                $name = $r->parent->name . ' ' . $name;
            }
            $managements[] = [
                'id'        => $r->id,
                'text'      => $name
            ];
        }

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()->detach( $request->get( 'management_id' ) );

    }

    public function managementsEmpty ( Request $request, $id )
    {

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

}
