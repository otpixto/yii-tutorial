<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\SegmentChilds;
use App\Classes\Title;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\Executor;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\SegmentType;
use App\Models\Type;
use Carbon\Carbon;
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

        if ( $request->ajax() || $request->get( 'export' ) == 1 )
        {

            $provider_id = $request->get( 'provider_id' );
            $category_id = $request->get( 'category_id' );
            $building_id = $request->get( 'building_id' );
            $type_id = $request->get( 'type_id' );
            $segment_id = $request->get( 'segment_id' );
            $parent_id = $request->get( 'parent_id' );

            $managements = Management
                ::mine()
                ->orderBy( Management::$_table . '.name' );

            if ( ! empty( $request->get( 'name' ) ) )
            {
                $s = '%' . str_replace( ' ', '%', trim( $request->get( 'name' ) ) ) . '%';
                $managements
                    ->where( Management::$_table . '.name', 'like', $s );
            }

            if ( ! empty( $request->get( 'phone' ) ) )
            {
                $p = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone' ) ), - 10 );
                $managements
                    ->where( function ( $q ) use ( $p )
                    {
                        $q
                            ->where( Management::$_table . '.phone', '=', $p )
                            ->orWhere( Management::$_table . '.phone2', '=', $p );
                    });
            }

            if ( ! empty( $category_id ) )
            {
                $managements
                    ->category( $category_id );
            }

            if ( ! empty( $segment_id ) )
            {
                $segment = Segment::find( $segment_id );
                if ( $segment )
                {
                    $segmentChilds = new SegmentChilds( $segment );
                    $segmentChildsIds = $segmentChilds->ids;
                    $managements
                        ->whereHas( 'building', function ( $building ) use ( $segmentChildsIds )
                        {
                            return $building
                                ->whereIn( Building::$_table . '.segment_id', $segmentChildsIds );
                        });
                }
            }

            if ( ! empty( $parent_id ) )
            {
                $managements
                    ->where( Management::$_table . '.parent_id', '=', $parent_id );
            }

            if ( ! empty( $provider_id ) )
            {
                $managements
                    ->where( Management::$_table . '.provider_id', '=', $provider_id );
            }

            if ( ! empty( $building_id ) )
            {
                $managements
                    ->whereHas( 'buildings', function ( $buildings ) use ( $building_id )
                    {
                        return $buildings
                            ->where( Building::$_table . '.id', '=', $building_id );
                    });
            }

            if ( ! empty( $type_id ) )
            {
                $managements
                    ->whereHas( 'types', function ( $types ) use ( $type_id )
                    {
                        return $types
                            ->where( Type::$_table . '.id', '=', $type_id );
                    });
            }

            if ( $request->get( 'export' ) == 1 )
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
                        'Адрес'                 => $management->building->name ?? '',
                        'График работы'         => $management->schedule,
                        'ФИО руководителя'      => $management->director,
                        'E-mail'                => $management->email,
                        'Сайт'                  => $management->site,
                    ];
                }
                \Excel::create( 'УО', function ( $excel ) use ( $data )
                {
                    $excel->sheet( 'УО', function ( $sheet ) use ( $data )
                    {
                        $sheet->fromArray( $data );
                    });
                })->export( 'xls' );
            }

            $managements = $managements
                ->with(
                    'parent'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            $providers = Provider
                ::mine()
                ->current()
                ->orderBy( 'name' )
                ->get();

            $this->addLog( 'Просмотрел список УО (стр.' . $request->get( 'page', 1 ) . ')' );

            return view( 'catalog.managements.parts.list' )
                ->with( 'managements', $managements )
                ->with( 'providers', $providers );

        }

        return view( 'catalog.managements.index' )
            ->with( 'request', $request );

    }

    public function searchForm ( Request $request )
    {

        if ( ! \Auth::user()->can( 'catalog.managements.search' ) )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Доступ запрещен' );
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        $parents = Management
            ::mine()
            ->whereNull( Management::$_table . '.parent_id' )
            ->orderBy( Management::$_table . '.name' )
            ->pluck( Management::$_table . '.name', Management::$_table . '.id' );

        return view( 'catalog.managements.parts.search' )
            ->with( 'providers', $providers ?? [] )
            ->with( 'parents', $parents ?? [] )
            ->with( 'categories', Management::$categories );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить УО' );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.managements.create' )
            ->with( 'providers', $providers );

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
            'provider_id'           => 'nullable|integer',
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'email'                 => 'nullable|email',
            'site'                  => 'nullable|url',
        ];

        $this->validate( $request, $rules );

        $old = Management
            ::mine( Management::IGNORE_MANAGEMENT )
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
                ->withErrors( [ 'УО уже существует' ] );
        }

        $management = Management::create( $request->all() );
        if ( $management instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $management );
        }
        $management->save();

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

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();

        return view( 'catalog.managements.edit' )
            ->with( 'management', $management )
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

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $rules = [
            'guid'                  => 'nullable|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|string|max:255',
            'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'email'                 => 'nullable|email',
            'site'                  => 'nullable|url',
        ];

        $this->validate( $request, $rules );

        $old = Management
            ::mine( Management::IGNORE_MANAGEMENT )
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
                ->withErrors( [ 'УО уже существует' ] );
        }

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

    public function contract ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $rules = [
            'has_contract'                  => 'required|boolean',
            'contract_number'               => 'nullable|string',
            'contract_begin'                => 'nullable|required_with:contract_end|date',
            'contract_end'                  => 'nullable|required_with:contract_begin|date',
        ];

        $this->validate( $request, $rules );

        $attributes = $request->all();

        if ( ! empty( $attributes[ 'contract_begin' ] ) )
        {
            $attributes[ 'contract_begin' ] = Carbon::parse( $attributes[ 'contract_begin' ] )->toDateTimeString();
        }

        if ( ! empty( $attributes[ 'contract_end' ] ) )
        {
            $attributes[ 'contract_end' ] = Carbon::parse( $attributes[ 'contract_end' ] )->toDateTimeString();
        }

        $res = $management->edit( $attributes );
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
        $building_id = $request->get( 'building_id' );
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        $managements = Management
			::mine()
            ->whereHas( 'types', function ( $types ) use ( $type_id )
            {
                return $types
                    ->where( Type::$_table . '.id', '=', $type_id );
            })
            ->whereHas( 'buildings', function ( $buildings ) use ( $building_id )
            {
                return $buildings
                    ->where( Building::$_table . '.id', '=', $building_id );
            });

        if ( ! empty( $provider_id ) )
        {
            $managements
                ->where( Management::$_table . '.provider_id', '=', $provider_id );
        }

        $managements = $managements->get();

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

    public function json ( Request $request )
    {

        $provider_id = trim( $request->get( 'provider_id', '' ) );

        $managements = Management
            ::mine()
            ->select(
                'id',
                'name as text'
            )
            ->orderBy( Management::$_table .'.name' );

        if ( ! empty( $provider_id ) )
        {
            $managements
                ->where( Management::$_table . '.provider_id', '=', $provider_id );
        }

        $managements = $managements->get();

        return $managements;

    }

    public function parentsSearch ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $managements = Management
            ::mine()
            ->whereNull( 'parent_id' )
            ->select(
                Management::$_table . '.id',
                Management::$_table . '.name AS text'
            )
            ->where( Management::$_table . '.name', 'like', $s )
            ->where( Management::$_table . '.id', '!=', $management->id )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        return $managements;

    }

    public function executorsSearch ( Request $request )
    {
        if ( $request->has( 'managements' ) )
        {
            $ids = $request->get( 'managements' );
        }
        else if ( $request->has( 'management_id' ) )
        {
            $ids = [ $request->get( 'management_id' ) ];
        }
        else
        {
            $ids = [];
        }
        $managements = Management::mine()->whereIn( Management::$_table . '.id', $ids )->get();
        if ( ! $managements->count() )
        {
            return false;
        }
        $executors = [];
        foreach ( $managements as $management )
        {
            $executors += $management->executors->toArray();
        }
        return $executors;
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

    public function buildings ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $management = Management::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementBuildings = $management->buildings()
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $managementBuildings
                ->where( Building::$_table . '.name', 'like', $s );
        }

        $managementBuildings = $managementBuildings
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $segmentsTypes = SegmentType::orderBy( 'sort' )->pluck( 'name', 'id' );
		
		$buildingTypes = BuildingType::mine()->orderBy( 'name' )->pluck( 'name', 'id' );

        return view( 'catalog.managements.buildings' )
            ->with( 'management', $management )
            ->with( 'search', $search )
            ->with( 'segmentsTypes', $segmentsTypes )
            ->with( 'buildingTypes', $buildingTypes )
            ->with( 'managementBuildings', $managementBuildings );

    }

    public function buildingsExport ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $management = Management::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementBuildings = $management->buildings()
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $managementBuildings
                ->where( Building::$_table . '.name', 'like', $s );
        }

        $managementBuildings = $managementBuildings->get();

        $data = [];
        $i = 0;
        foreach ( $managementBuildings as $building )
        {
            $data[ $i ] = [
                'id дома'                    => $building->id,
                'Наименование адреса'        => $building->name,
                'Тип (дом/бизнес-центр)'     => $building->buildingType->name ?? '',
                'Наименование сегмента'      => $building->getSegments()->implode( 'name', ', ' ) ?? '',
                'GUID'                       => $building->guid,
            ];
            $i ++;
        }

        $this->addLog( 'Выгрузил список зданий' );

        \Excel::create( 'ЗДАНИЯ', function ( $excel ) use ( $data )
        {
            $excel->sheet( 'ЗДАНИЯ', function ( $sheet ) use ( $data )
            {
                $sheet->fromArray( $data );
            });
        })->export( 'xls' );

        die;

    }

    public function buildingsSearch ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
		
		$provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        $res = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->where( Building::$_table . '.name', 'like', $s )
			->whereNotIn( Building::$_table . '.id', $management->buildings()->pluck( Building::$_table . '.id' ) )
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

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->buildings()->attach( $request->get( 'buildings', [] ) );

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

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->back()
                ->withErrors( [ 'УО не найдена' ] );
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
                    ::mine( Building::IGNORE_MANAGEMENT )
                    ->whereIn( Building::$_table . '.segment_id', $segmentsIds );
                if ( ! empty( $request->get( 'type_id' ) ) )
                {
                    $buildings
                        ->where( Building::$_table . '.building_type_id', '=', $request->get( 'type_id' ) );
                }
                $buildings = $buildings->get();
                foreach ( $buildings as $building )
                {
                    if ( ! $management->buildings->contains( $building->id ) )
                    {
                        $management->buildings()->attach( $building->id );
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

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->buildings()->detach( $request->get( 'building_id' ) );

    }

    public function buildingsEmpty ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->buildings()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function executors ( Request $request, $id )
    {

        Title::add( 'Исполнители' );

        $management = Management::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementExecutors = $management->executors()
            ->orderBy( Executor::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $managementExecutors
                ->where( Executor::$_table . '.name', 'like', $s );
        }

        $managementExecutors = $managementExecutors
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        return view( 'catalog.managements.executors' )
            ->with( 'search', $search )
            ->with( 'management', $management )
            ->with( 'managementExecutors', $managementExecutors );
    }

    public function executorsAdd ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $executor = $management->addExecutor( $request->get( 'name' ), $request->get( 'phone' ) );

        if ( $executor instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $executor );
        }

        $executor->save();

        return redirect()->back()
            ->with( 'success', 'Исполнитель успешно добавлен' );

    }

    public function executorsDel ( Request $request, $id )
    {

        $rules = [
            'executor_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $executor = $management->executors()->find( $request->get( 'executor_id' ) );
        if ( ! $executor )
        {
            return redirect()->back()
                ->withErrors( [ 'Исполнитель не найден' ] );
        }

        $executor->delete();

    }

    public function executorsEmpty ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        foreach ( $management->executors as $executor )
        {
            $executor->delete();
        }

        return redirect()->back()
            ->with( 'success', 'Исполнители успешно удалены' );

    }

    public function types ( Request $request, $id )
    {

        Title::add( 'Привязка Классификатора' );

        $management = Management::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $managementTypes = $management->types()
            ->orderBy( Type::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $managementTypes
                ->where( Type::$_table . '.name', 'like', $s );
        }

        $managementTypes = $managementTypes
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $res = Type
            ::mine()
            ->where( function ( $q ) use ( $management )
            {
                return $q
                    ->whereNull( 'provider_id' )
                    ->orWhere( 'provider_id', '=', $management->provider_id );
            })
            ->whereNotIn( 'id', $management->types()->pluck( Type::$_table . '.id' ) )
            ->get()
            ->sortBy( 'name' );
        $availableTypes = [];
        foreach ( $res as $r )
        {
            $availableTypes[ $r->parent->name ?? 'Без родителя' ][ $r->id ] = $r->name;
        }

        return view( 'catalog.managements.types' )
            ->with( 'management', $management )
            ->with( 'search', $search )
            ->with( 'managementTypes', $managementTypes )
            ->with( 'availableTypes', $availableTypes );

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

    public function typesEmpty ( Request $request, $id )
    {

        $management = Management::find( $id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $management->types()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function act ( Request $request, $management_id, $act_id )
    {

        $management = Management::find( $management_id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.index' )
                ->withErrors( [ 'УО не найдена' ] );
        }

        $act = $management->acts()->find( $act_id );

        if ( ! $management )
        {
            return redirect()->route( 'managements.edit', $management->id )
                ->withErrors( [ 'Акт не найден' ] );
        }

        return view( 'catalog.managements.act' )
            ->with( 'act', $act );

    }

}
