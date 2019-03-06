<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
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
        $parent_id = trim( $request->get( 'parent_id', '' ) );
        $building_id = trim( $request->get( 'building_id', '' ) );
        $management_id = trim( $request->get( 'management_id', '' ) );
        $provider_id = trim( $request->get( 'provider_id', '' ) );

        $types = Type
            ::mine()
            ->select(
                'types.*',
                'parent_type.name AS parent_name'
            )
            ->leftJoin( 'types AS parent_type', 'parent_type.id', '=', 'types.parent_id' )
            ->orderBy( Type::$_table .'.name' );

        if ( ! empty( $parent_id ) )
        {
            $types
                ->where( function ( $q ) use ( $parent_id )
                {
                    return $q
                        ->where( Type::$_table . '.parent_id', '=', $parent_id )
                        ->orWhere( Type::$_table . '.id', '=', $parent_id );
                });
        }

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $types
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( Type::$_table . '.name', 'like', $s )
                        ->orWhere( Type::$_table . '.guid', 'like', $s )
                        ->orWhere( 'parent_type.name', 'like', $s )
                        ->orWhere( 'parent_type.guid', 'like', $s );
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
                'parent',
                'managements'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $parents = Type
            ::mine()
            ->whereNull( 'parent_id' )
            ->orderBy( 'name' )
            ->get();

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        $this->addLog( 'Просмотрел классификатор (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.types.index' )
            ->with( 'types', $types )
            ->with( 'parents', $parents )
            ->with( 'providers', $providers );

    }

    public function json ( Request $request )
    {

        $provider_id = trim( $request->get( 'provider_id', '' ) );

        $types = Type
            ::mine()
            ->select(
                'id',
                'name as text'
            )
            ->orderBy( Type::$_table .'.name' );

        if ( ! empty( $provider_id ) )
        {
            $types
                ->where( Type::$_table . '.provider_id', '=', $provider_id );
        }

        if ( $request->get( 'works' ) )
        {
            $types
                ->where( Type::$_table . '.works', '=', 1 );
        }

        $types = $types->get();

        return $types;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить Классификатор' );

        $parents = Type
            ::mine()
            ->whereNull( 'parent_id' )
            ->orderBy( Type::$_table . '.name' )
            ->pluck( Type::$_table . '.name', Type::$_table . '.id' );

        return view( 'catalog.types.create' )
            ->with( 'parents', $parents );
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
            'guid'                  => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|string|max:255',
            'parent_id'             => 'nullable|integer',
            'period_acceptance'     => 'numeric',
            'period_execution'      => 'numeric',
            'need_act'              => 'boolean',
            'is_pay'                => 'boolean',
            'emergency'             => 'boolean',
        ];

        $this->validate( $request, $rules );

        $old = Type
            ::mine()
            ->where( function ( $q ) use ( $request )
            {
                $q
                    ->where( 'name', '=', $request->get( 'name' ) );
                if ( ! empty( $request->get( 'guid' ) ) )
                {
                    $q
                        ->orWhere( 'guid', '=', $request->get( 'guid' ) );
                }
                return $q;
            })
            ->first();
        if ( $old )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Классификатор уже существует' ] );
        }

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

        $type = Type::mine()->find( $id );

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
            ->with( 'parents', $parents );
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
            'guid'                  => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required_with:category_id|string|max:255',
            'parent_id'             => 'nullable|integer',
            'period_acceptance'     => 'numeric',
            'period_execution'      => 'numeric',
            'price'                 => 'nullable|numeric',
            'color'                 => 'nullable|regex:/\#(.*){6}/',
            'need_act'              => 'boolean',
            'is_pay'                => 'boolean',
            'emergency'             => 'boolean',
            'works'                 => 'boolean',
            'lk'                    => 'boolean',
        ];

        $this->validate( $request, $rules );

        $old = Type
            ::mine()
            ->where( 'id', '!=', $type->id )
            ->where( function ( $q ) use ( $request )
            {
                $q
                    ->where( 'name', '=', $request->get( 'name' ) );
                if ( ! empty( $request->get( 'guid' ) ) )
                {
                    $q
                        ->orWhere( 'guid', '=', $request->get( 'guid' ) );
                }
                return $q;
            })
            ->first();
        if ( $old )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Классификатор уже существует' ] );
        }

        $attributes = $request->all();
        $attributes[ 'need_act' ] = $request->get( 'need_act', 0 );
        $attributes[ 'emergency' ] = $request->get( 'emergency', 0 );
        $attributes[ 'is_pay' ] = $request->get( 'is_pay', 0 );
        $attributes[ 'works' ] = $request->get( 'works', 0 );
        $attributes[ 'lk' ] = $request->get( 'lk', 0 );

        $res = $type->edit( $attributes );
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
            ::mine()
            ->find( $request->get( 'type_id' ) );

        $type->category_name = $type->parent->name ?? $type->name;
        if ( $type->description )
        {
            $type->description = nl2br( $type->description );
        }

        return $type;

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка УО' );

        $type = Type::mine()->find( $id );

        if ( ! $type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $search = trim( $request->get( 'search', '' ) );

        $typeManagements = $type->managements()
            ->orderBy( Management::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $typeManagements
                ->where( Management::$_table . '.name', 'like', $s );
        }

        $typeManagements = $typeManagements
            ->paginate( config( 'pagination.per_page' ) )
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

    public function fix ( Request $request )
    {
        $types = Type::mine()->get();
        foreach ( $types as $type )
        {
            if ( $type->category && ! $type->parent )
            {
                $newType = Type
                    ::mine()
                    ->where( 'name', '=', $type->category->name )
                    ->first();
                if ( ! $newType )
                {
                    $newType = Type::create([
                        'provider_id'       => $type->provider_id,
                        'name'              => $type->category->name
                    ]);
                    $newType->save();
                }
                $type->parent_id = $newType->id;
                $type->save();
            }
        }
    }

}
