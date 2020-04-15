<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Management;
use App\Models\Type;
use App\Models\TypeGroup;
use App\Models\Vendor;
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
        $group_id = trim( $request->get( 'group_id', '' ) );

        $types = Type
            ::mine()
            ->select(
                'types.*',
                'parent_type.name AS parent_name'
            )
            ->leftJoin( 'types AS parent_type', 'parent_type.id', '=', 'types.parent_id' )
            ->orderBy( Type::$_table . '.name' );

        if ( ! empty( $parent_id ) )
        {
            $types
                ->where( function ( $q ) use ( $parent_id )
                {
                    return $q
                        ->where( Type::$_table . '.parent_id', '=', $parent_id )
                        ->orWhere( Type::$_table . '.id', '=', $parent_id );
                } );
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
                } );
        }

        if ( ! empty( $building_id ) )
        {
            $types
                ->whereHas( 'buildings', function ( $buildings ) use ( $building_id )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $building_id );
                } );
        }

        if ( ! empty( $management_id ) )
        {
            $types
                ->whereHas( 'managements', function ( $managements ) use ( $management_id )
                {
                    return $managements
                        ->where( Management::$_table . '.id', '=', $management_id );
                } );
        }

        if ( ! empty( $provider_id ) )
        {
            $types
                ->where( Type::$_table . '.provider_id', '=', $provider_id );
        }

        if ( ! empty( $group_id ) )
        {
            $types
                ->where( Type::$_table . '.group_id', '=', $group_id );
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

        $this->addLog( 'Просмотрел классификатор (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.types.index' )
            ->with( 'types', $types )
            ->with( 'parents', $parents );

    }

    public function json ( Request $request )
    {

        $provider_id = trim( $request->get( 'provider_id', '' ) );

        $isWithVendorID = trim( $request->get( 'is_with_vendor_id', '' ) );

        $isWithParentID = trim( $request->get( 'is_with_parent_id', '' ) );

        $types = Type
            ::mine()
            ->select(
                Type::$_table . '.id as id',
                'name as text'
            )
            ->orderBy( Type::$_table . '.name' );

        if ( ! empty( $provider_id ) )
        {
            if ( $isWithVendorID == 'true' )
            {
                $types
                    ->leftJoin( 'types_vendors', Type::$_table . '.id', '=', 'types_vendors.type_id' )
                    ->whereNull( Type::$_table . '.parent_id' )
                    ->where( 'types_vendors.vendor_id', $provider_id );
            } elseif ( $isWithParentID == 'true' )
            {
                $types
                    ->where( Type::$_table . '.parent_id', '=', $provider_id );
            } else
            {
                $types
                    ->where( Type::$_table . '.provider_id', '=', $provider_id );
            }

        } else
        {
            if ( $isWithVendorID == 'true' )
            {
                $types
                    ->leftJoin( 'types_vendors', Type::$_table . '.id', '=', 'types_vendors.type_id' )
                    ->whereNull( Type::$_table . '.parent_id' )
                    ->where( 'types_vendors.vendor_id', Vendor::DEFAULT_VENDOR_ID );
            } else
            {
                $defaultParentsIDs = Type::
                leftJoin( 'types_vendors', Type::$_table . '.id', '=', 'types_vendors.type_id' )
                    ->where( 'types_vendors.vendor_id', Vendor::DEFAULT_VENDOR_ID )
                    ->pluck( Type::$_table . '.id as id' )
                    ->toArray();

                $types = Type
                    ::mine()
                    ->select( 'id', 'name as text' )
                    ->orderByDesc( Type::$_table . '.tickets_using_times' )
                    ->orderBy( Type::$_table . '.name' )
                    ->whereIn( Type::$_table . '.parent_id', $defaultParentsIDs )
                    ->get()
                    ->toArray();

                //$types = ( new Type() )->sortByUsersFavoriteTypes( $types );

                return $types;
            }

        }

        if ( $request->get( 'works' ) )
        {
            $types
                ->where( Type::$_table . '.works', '=', 1 );
        }

        $types = $types->get()
            ->values();

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $rules = [
            'guid' => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'period_acceptance' => 'numeric',
            'period_execution' => 'numeric',
            'need_act' => 'boolean',
            'is_pay' => 'boolean',
            'emergency' => 'boolean',
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
            } )
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
            return redirect()
                ->back()
                ->withErrors( $type );
        }
        $type->save();

        self::clearCache();

        return redirect()
            ->route( 'types.edit', $type->id )
            ->with( 'success', 'Классификатор успешно добавлен' );

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        Title::add( 'Редактировать Классификатор' );

        $type = Type::mine()
            ->find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $parents = Type
            ::mine()
            ->whereNull( 'parent_id' )
            ->where( 'id', '!=', $type->id )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $groups = TypeGroup
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $vendors = Vendor::where('name', '!=', "ГЖИ")->orderByDesc('id')->pluck('name', 'id')->toArray();

        return view( 'catalog.types.edit' )
            ->with( 'type', $type )
            ->with( 'parents', $parents )
            ->with( 'vendors', $vendors )
            ->with( 'groups', $groups );
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

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $rules = [
            'guid' => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name' => 'required_with:category_id|string|max:255',
            'parent_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'period_acceptance' => 'numeric',
            'period_execution' => 'numeric',
            'price' => 'nullable|numeric',
            'color' => 'nullable|regex:/\#(.*){6}/',
            'need_act' => 'boolean',
            'is_pay' => 'boolean',
            'emergency' => 'boolean',
            'works' => 'boolean',
            'lk' => 'boolean',
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
            } )
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

        $vendors = $request->get( 'vendors', '' );

        if ( count( $vendors ) )
        {
            $type->vendors()
                ->detach();

            if ( is_array( $vendors ) )
            {
                foreach ( $vendors as $vendorID )
                {
                    \Illuminate\Support\Facades\DB::table( 'types_vendors' )
                        ->insert(
                            [ 'type_id' => $type->id, 'vendor_id' => (int) $vendorID ]
                        );
                }
            }

        }

        $res = $type->edit( $attributes );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()
            ->route( 'types.edit', $type->id )
            ->with( 'success', 'Классификатор успешно отредактирован' );

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

        $type = Type::mine()
            ->find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
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
            ->whereNotIn( Management::$_table . '.id', $type->managements()
                ->pluck( Management::$_table . '.id' ) )
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
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $res = Management
            ::mine()
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $type->managements()
                ->pluck( Management::$_table . '.id' ) )
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
                'id' => $r->id,
                'text' => $name
            ];
        }

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()
            ->attach( $request->get( 'managements' ) );

        return redirect()
            ->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id' => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()
            ->detach( $request->get( 'management_id' ) );

    }

    public function managementsEmpty ( Request $request, $id )
    {

        $type = Type::find( $id );

        if ( ! $type )
        {
            return redirect()
                ->route( 'types.index' )
                ->withErrors( [ 'Классификатор не найден' ] );
        }

        $type->managements()
            ->detach();

        return redirect()
            ->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function fix ( Request $request )
    {
        $types = Type::mine()
            ->get();
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
                    $newType = Type::create( [
                        'provider_id' => $type->provider_id,
                        'name' => $type->category->name
                    ] );
                    $newType->save();
                }
                $type->parent_id = $newType->id;
                $type->save();
            }
        }
    }

    public function massManagementsEdit ( Request $request )
    {

        Title::add( 'Привязка классификаторов к УО' );

        $id = $request->get( 'management_id', null );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()
                ->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementTypes = $management->types()
            ->orderBy( Type::$_table . '.name' );

        $managementTypesListString = $managementTypes->get()
            ->pluck( 'id' )
            ->implode( ',' );

        $availableManagements = Management
            ::mine()
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? 'Без родителя' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'catalog.types.mass-edit' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'management_id', $id )
            ->with( 'managementTypesListString', $managementTypesListString );
    }

    public function massManagementsAdd ( Request $request )
    {

        $managementID = $request->get( 'management_id', null );

        try
        {

            $typesJSON = (string) $request->get( 'types', '' );

            $types = explode( ',', $typesJSON );

            $managements = $request->get( 'managements', [] );

            if ( is_array( $managements ) && count( $types ) )
            {
                foreach ( $managements as $management_id )
                {
                    foreach ( $types as $type_id )
                    {
                        $managementsType = \Illuminate\Support\Facades\DB::table( 'managements_types' )
                            ->where( 'management_id', $management_id )
                            ->where( 'type_id', $type_id )
                            ->first();

                        if ( ! $managementsType )
                        {

                            \Illuminate\Support\Facades\DB::table( 'managements_types' )
                                ->insert(
                                    [
                                        'management_id' => $management_id,
                                        'type_id' => $type_id
                                    ]
                                );
                        }
                    }
                }
            }
        }
        catch ( \Exception $exception )
        {
            dd( $exception->getMessage() );
            return redirect()
                ->route( 'managements.types', [ 'management_id' => $managementID ] )
                ->with( 'error', 'Ошибка привязки классификаторов к выбранным УО' );
        }

        return redirect()
            ->route( 'managements.types', [ 'management_id' => $managementID ] )
            ->with( 'success', 'Классификаторы успешно привязаны к выбранным УО' );
    }

}
