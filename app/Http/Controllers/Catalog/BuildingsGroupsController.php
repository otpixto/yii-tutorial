<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\SegmentChilds;
use App\Classes\Title;
use App\Models\Building;
use App\Models\BuildingGroup;
use App\Models\BuildingType;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\SegmentType;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class BuildingsGroupsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Группы адресов' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $provider_id = $request->get( 'provider_id' );

        $groups = BuildingGroup
            ::mine()
            ->orderBy( BuildingGroup::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $groups
                ->where( BuildingGroup::$_table . '.name', 'like', $s );
        }

        if ( ! empty( $provider_id ) )
        {
            $groups
                ->where( BuildingGroup::$_table . '.provider_id', '=', $provider_id );
        }

        $groups = $groups
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $this->addLog( 'Просмотрел список групп (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.groups.buildings.index' )
            ->with( 'groups', $groups );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить группу' );
        return view( 'catalog.groups.buildings.create' );
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

        $group = BuildingGroup::create( $request->all() );

        if ( $group instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $group );
        }

        $group->save();

        self::clearCache();

        return redirect()->route( 'buildings_groups.index' )
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

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->route( 'buildings_groups.index' )
                ->withErrors( [ 'Группа не найдена' ] );
        }

        return view( 'catalog.groups.buildings.edit' )
            ->with( 'group', $group );

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

        $group = BuildingGroup::find( $id );

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

        return redirect()->route( 'buildings_groups.edit', $group->id )
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

    public function buildings ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $group = BuildingGroup::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $group )
        {
            return redirect()->route( 'buildings_groups.index' )
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $groupBuildings = $group->buildings()
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $groupBuildings
                ->where( Building::$_table . '.name', 'like', $s );
        }

        $groupBuildings = $groupBuildings
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $segmentsTypes = SegmentType::orderBy( 'sort' )->pluck( 'name', 'id' );

        $buildingTypes = BuildingType::mine()->orderBy( 'name' )->pluck( 'name', 'id' );

        return view( 'catalog.groups.buildings.buildings' )
            ->with( 'group', $group )
            ->with( 'search', $search )
            ->with( 'segmentsTypes', $segmentsTypes )
            ->with( 'buildingTypes', $buildingTypes )
            ->with( 'groupBuildings', $groupBuildings );

    }

    public function buildingsSearch ( Request $request, $id )
    {

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        $res = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->where( Building::$_table . '.name', 'like', $s )
            ->whereNotIn( Building::$_table . '.id', $group->buildings()->pluck( Building::$_table . '.id' ) )
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $provider_id ) )
        {
            $res
                ->where( Building::$_table . '.provider_id', '=', $provider_id );
        }

        $res = $res
            ->with( 'buildingType' )
            ->get();

        $buildings = [];
        foreach ( $res as $r )
        {
            $buildings[] = [
                'id' => $r->id,
                'text' => $r->name . ' (' . $r->buildingType->name . ')'
            ];
        }

        return $buildings;

    }

    public function buildingsAdd ( Request $request, $id )
    {

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->buildings()->attach( $request->get( 'buildings', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно назначены' );

    }

    public function segmentsAdd ( Request $request, $id )
    {

        $rules = [
            'segments'                  => 'required|array',
            'type_id'             	    => 'nullable|integer',
        ];

        $this->validate( $request, $rules );

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        if ( ! empty( $request->get( 'segments' ) ) )
        {
            $segments = Segment::whereIn( 'id', $request->get( 'segments' ) )->get();
            if ( $segments->count() )
            {
                $segmentsIds = [];
                foreach ( $segments as $segment )
                {
                    $segmentChilds = new SegmentChilds( $segment );
                    $segmentsIds += $segmentChilds->ids;
                }
                $buildings = Building
                    ::mine()
                    ->whereIn( Building::$_table . '.segment_id', $segmentsIds );
                if ( ! empty( $request->get( 'type_id' ) ) )
                {
                    $buildings
                        ->where( Building::$_table . '.building_type_id', '=', $request->get( 'type_id' ) );
                }
                $buildings = $buildings->get();
                foreach ( $buildings as $building )
                {
                    if ( ! $group->buildings->contains( $building->id ) )
                    {
                        $group->buildings()->attach( $building->id );
                    }
                }
            }
        }

        return redirect()->back()
            ->with( 'success', 'Здания сегментов успешно привязаны' );

    }

    public function buildingsDel ( Request $request, $id )
    {

        $rules = [
            'building_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->buildings()->detach( $request->get( 'building_id' ) );

    }

    public function buildingsEmpty ( Request $request, $id )
    {

        $group = BuildingGroup::find( $id );

        if ( ! $group )
        {
            return redirect()->back()
                ->withErrors( [ 'Группа не найдена' ] );
        }

        $group->buildings()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function select ( Request $request )
    {
        $groups = BuildingGroup
            ::mine()
            ->orderBy( 'name' )
            ->whereHas( 'buildings' )
            ->with( 'buildings' )
            ->get();
        return view( 'catalog.groups.buildings.select' )
            ->with( 'groups', $groups )
            ->with( 'selector', $request->get( 'selector' ) );
    }

}
