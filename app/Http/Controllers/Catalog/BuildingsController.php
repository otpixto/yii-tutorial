<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Building;
use App\Models\BuildingRoom;
use App\Models\BuildingType;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class BuildingsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Здания' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $provider_id = $request->get( 'provider_id' );
        $segment_id = $request->get( 'segment_id' );
        $building_type_id = $request->get( 'building_type_id' );
        $management_id = $request->get( 'management_id' );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->get();

        $buildings = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $buildings
                ->where( Building::$_table . '.name', 'like', $s )
                ->orWhere( Building::$_table . '.guid', 'like', $s )
                ->orWhere( Building::$_table . '.hash', '=', Building::genHash( $search ) );
        }

        if ( ! empty( $provider_id ) )
        {
            $buildings
                ->where( Building::$_table . '.provider_id', '=', $provider_id );
        }

        if ( ! empty( $segment_id ) )
        {
            $buildings
                ->where( Building::$_table . '.segment_id', '=', $segment_id );
        }

        if ( ! empty( $building_type_id ) )
        {
            $buildings
                ->where( Building::$_table . '.building_type_id', '=', $building_type_id );
        }

        if ( ! empty( $management_id ) )
        {
            $buildings
                ->whereHas( 'managements', function ( $q ) use ( $management_id )
                {
                    return $q
                        ->where( Management::$_table . '.id', '=', $management_id );
                });
        }

        $buildings = $buildings
            ->with(
                'buildingType',
                'managements'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $buildingTypes = BuildingType
            ::orderBy( 'name' )
            ->get();

        $this->addLog( 'Просмотрел список зданий (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.buildings.index' )
            ->with( 'buildings', $buildings )
            ->with( 'buildingTypes', $buildingTypes )
            ->with( 'providers', $providers );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить здание' );
        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        $buildingTypes = BuildingType
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
        return view( 'catalog.buildings.create' )
            ->with( 'providers', $providers )
            ->with( 'buildingTypes', $buildingTypes );
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
            'segment_id'            => 'required|integer',
            'building_type_id'      => 'required|integer',
            'guid'                  => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|max:255',
            'number'                => 'required',
        ];

        $this->validate( $request, $rules );

        $building = Building::create( $request->all() );

        if ( $building instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $building );
        }

        $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $building->name ) ) );
        if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
        {
            $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
            $building->lon = $pos[0];
            $building->lat = $pos[1];
        }
        else
        {
            $building->lon = -1;
            $building->lat = -1;
        }

        $building->save();

        self::clearCache();

        return redirect()->route( 'buildings.index' )
            ->with( 'success', 'Здание успешно добавлено' );

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

        Title::add( 'Редактировать здание' );

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $segments = $building->getSegments();

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        if ( ( ! $building->lon || ! $building->lat ) && $building->lon != -1 && $building->lat != -1 )
        {
            $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $building->name ) ) );
            if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
            {
                $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
                $building->lon = $pos[0];
                $building->lat = $pos[1];
            }
            else
            {
                $building->lon = -1;
                $building->lat = -1;
            }
            $building->save();
        }

        $buildingTypes = BuildingType
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'catalog.buildings.edit' )
            ->with( 'building', $building )
            ->with( 'providers', $providers )
            ->with( 'segments', $segments )
            ->with( 'buildingTypes', $buildingTypes );

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

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }
		
		$rules = [
            'provider_id'           => 'required|integer',
            'building_type_id'      => 'required|integer',
            'guid'                  => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|max:255',
            'date_of_construction'  => 'nullable|date',
            'porches_count'         => 'nullable|integer',
            'floor_count'           => 'nullable|integer',
            'room_total_count'      => 'nullable|integer',
            'first_floor_index'     => 'nullable|integer',
            'is_first_floor_living' => 'nullable|boolean',
        ];

        $this->validate( $request, $rules );

        $attributes = $request->all();

        if ( $building->name != $attributes[ 'name' ] )
        {
            $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $attributes[ 'name' ] ) ) );
            if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
            {
                $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
                $attributes[ 'lon' ] = $pos[0];
                $attributes[ 'lat' ] = $pos[1];
            }
            else
            {
                $attributes[ 'lon' ] = -1;
                $attributes[ 'lat' ] = -1;
            }
        }

        $res = $building->edit( $attributes );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'buildings.edit', $building->id )
            ->with( 'success', 'Здание успешно отредактировано' );

    }

    public function storeRooms ( Request $request, $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        if ( $building->room_total_count > 0 && $building->porches_count > 0 && $building->floor_count > 0 )
        {

            $rooms_by_floor = ceil( abs( $building->room_total_count / ( $building->is_first_floor_living ? $building->floor_count : $building->floor_count - 1 ) / $building->porches_count ) );
            $room_number = 0;

            $buildingRooms = $building->rooms;

            for ( $porch = 1; $porch <= $building->porches_count; $porch ++ )
            {
                for ( $floor = $building->first_floor_index; $floor <= $building->floor_count; $floor ++ )
                {
                    if ( $floor == $building->first_floor_index && ! $building->is_first_floor_living ) continue;
                    for ( $i = 1; $i <= $rooms_by_floor; $i ++ )
                    {
                        ++ $room_number;
                        $buildingRoom = $buildingRooms->where( 'number', $room_number )->first();
                        if ( ! $buildingRoom )
                        {
                            $buildingRoom = BuildingRoom
                                ::create([
                                    'building_id'       => $building->id,
                                    'floor'             => $floor,
                                    'porch'             => $porch,
                                    'number'            => $room_number,
                                    'living_area'       => 0,
                                    'total_area'        => 0,
                                ]);
                            $buildingRoom->save();
                        }
                        else
                        {
                            $buildingRoom->edit([
                                'floor'             => $floor,
                                'porch'             => $porch,
                                'number'            => $room_number,
                            ]);
                        }
                    }
                }
            }

            $building
                ->rooms()
                ->where( 'number', '>', $room_number )
                ->orWhere( 'floor', '<', $building->first_floor_index )
                ->orWhere( 'floor', '>', $building->floor_count )
                ->orWhere( 'porch', '>', $building->porches_count )
                ->delete();

        }

        return redirect()->route( 'buildings.edit', $building->id )
            ->with( 'success', 'Комнаты успешно пересчитаны' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->delete();

        return redirect()->route( 'buildings.index' )
            ->with( 'success', 'Здание успешно удалено' );

    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        $res = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->leftJoin( BuildingType::$_table, BuildingType::$_table . '.id', '=', Building::$_table . '.building_type_id' )
            ->select(
                Building::$_table . '.id',
                \DB::raw( 'CONCAT_WS( \' \', ' . Building::$_table . '.name, CONCAT( \'(\', ' . BuildingType::$_table . '.name, \')\' ) ) AS fullname' )
            )
            ->having( 'fullname', 'like', $s )
            ->orderBy( 'fullname' );

        if ( ! empty( $provider_id ) )
        {
            $res
                ->where( Building::$_table . '.provider_id', '=', $provider_id );
        }

        $res = $res
            ->get();
			
		$buildings = [];
		foreach ( $res as $r )
		{
			$buildings[] = [
				'id' => $r->id,
				'text' => $r->fullname
			];
		}

        return $buildings;

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка УО' );

        $building = Building::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $buildingManagements = $building->managements()
            ->orderBy( Management::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $buildingManagements
                ->where( Management::$_table . '.name', 'like', $s );
        }

        $buildingManagements = $buildingManagements
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $availableManagements = Management
            ::mine()
            ->whereNotIn( Management::$_table . '.id', $building->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? 'Без родителя' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'catalog.buildings.managements' )
            ->with( 'building', $building )
            ->with( 'search', $search )
            ->with( 'buildingManagements', $buildingManagements )
            ->with( 'availableManagements', $availableManagements );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $res = Management
            ::mine()
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $building->managements()->pluck( Management::$_table . '.id' ) )
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

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->managements()->detach( $request->get( 'management_id' ) );

    }

    public function managementsEmpty ( Request $request, $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->managements()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function providers ( Request $request, $id )
    {

        Title::add( 'Привязка поставщиков' );

        $search = trim( $request->get( 'search', '' ) );

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $buildingProviders = $building->providers()
            ->orderBy( Provider::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $buildingProviders
                ->where( Type::$_table . '.name', 'like', $s );
        }

        $buildingProviders = $buildingProviders
            ->paginate( 30 )
            ->appends( $request->all() );

        $providers = Provider
            ::mine()
            ->whereNotIn( Provider::$_table . '.id', $building->providers()->pluck( Provider::$_table . '.id' ) )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        return view( 'catalog.buildings.providers' )
            ->with( 'building', $building )
            ->with( 'search', $search )
            ->with( 'buildingProviders', $buildingProviders )
            ->with( 'providers', $providers );

    }

    public function providersAdd ( Request $request, $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->providers()->attach( $request->get( 'providers' ) );

        return redirect()->route( 'buildings.providers', $building->id )
            ->with( 'success', 'Привязка прошла успешно' );

    }

    public function providersDel ( Request $request, $id )
    {

        $rules = [
            'provider_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->providers()->detach( $request->get( 'provider_id' ) );

    }

    public function providersEmpty ( Request $request, $id )
    {

        $building = Building::find( $id );

        if ( ! $building )
        {
            return redirect()->route( 'buildings.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $building->providers()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

}
