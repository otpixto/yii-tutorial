<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\SegmentChilds;
use App\Classes\Title;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\TypeGroup;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\SegmentType;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class TypesGroupsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Группы классификатора' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $provider_id = $request->get( 'provider_id' );

        $groups = TypeGroup
            ::mine()
            ->orderBy( TypeGroup::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $groups
                ->where( TypeGroup::$_table . '.name', 'like', $s );
        }

        if ( ! empty( $provider_id ) )
        {
            $groups
                ->where( TypeGroup::$_table . '.provider_id', '=', $provider_id );
        }

        $groups = $groups
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->get();

        $this->addLog( 'Просмотрел список групп (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.groups.types.index' )
            ->with( 'groups', $groups )
            ->with( 'providers', $providers );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить группу' );
        $providers = Provider
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        $models = [
            Building::class => 'Здания',
            Type::class => 'Классификатор',
        ];
        return view( 'catalog.groups.types.create' )
            ->with( 'providers', $providers )
            ->with( 'models', $models );
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
            'provider_id'           => 'required|integer',
            'name'                  => 'required|max:255',
        ];

        $this->validate( $request, $rules );

        $group = TypeGroup::create( $request->all() );

        if ( $group instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $group );
        }

        $group->save();

        self::clearCache();

        return redirect()->route( 'types_groups.index' )
            ->with( 'success', 'Группа успешно добавлена' );

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

        Title::add( 'Редактировать группы' );

        $group = TypeGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->route( 'types_groups.index' )
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $providers = Provider
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'catalog.groups.types.edit' )
            ->with( 'group', $group )
            ->with( 'providers', $providers );

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

        $group = TypeGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Группа не найдена' ] );
        }
		
		$rules = [
            'provider_id'           => 'required|integer',
            'name'                  => 'required|unique:types_groups,name,' . $group->id . '|max:255',
        ];

        $this->validate( $request, $rules );

        $res = $group->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'types_groups.edit', $group->id )
            ->with( 'success', 'Группа успешно отредактирована' );

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

    public function types ( Request $request, $id )
    {

        Title::add( 'Привязка Классификатора' );

        $group = TypeGroup::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $group )
        {
            return redirect()->route( 'types_groups.index' )
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $groupTypes = $group->types()
            ->orderBy( Type::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $groupTypes
                ->where( Type::$_table . '.name', 'like', $s );
        }

        $groupTypes = $groupTypes
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $res = Type
            ::mine()
            ->where( 'provider_id', '=', Provider::getCurrent()->id ?? null )
            ->whereNotIn( 'id', $group->types->pluck( 'id' ) )
            ->whereHas( 'parent' )
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableTypes = [];
        foreach ( $res as $r )
        {
            $availableTypes[ $r->parent->name ][ $r->id ] = $r->name;
        }

        return view( 'catalog.groups.types.list' )
            ->with( 'group', $group )
            ->with( 'search', $search )
            ->with( 'groupTypes', $groupTypes )
            ->with( 'availableTypes', $availableTypes );

    }

    public function typesAdd ( Request $request, $id )
    {

        $group = TypeGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->types()->attach( $request->get( 'types', [] ) );

        return redirect()->back()
            ->with( 'success', 'Классификатор успешно назначен' );

    }

    public function typesDel ( Request $request, $id )
    {

        $rules = [
            'type_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $group = TypeGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->types()->detach( $request->get( 'type_id' ) );

    }

    public function typesEmpty ( Request $request, $id )
    {

        $group = TypeGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->types()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

}
