<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Category;
use App\Models\Executor;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\Type;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class WorksController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Отключения' );
    }

    public function index ( Request $request )
    {

        if ( $request->ajax() || ! empty( $request->get( 'export' ) ) )
        {

            $managements = [];
            $filters = [];

            if ( ! empty( $request->get( 'managements' ) ) )
            {
                $managements = explode( ',', $request->get( 'managements' ) );
            }

            $works = Work
                ::mine()
                ->orderBy( Work::$_table . '.id', 'desc' );

            switch ( $request->get( 'show' ) )
            {
                case 'all':
                    $filters[] = 'Отключения за все время';
                    break;
                case 'overdue':
                    $works
                        ->current()
                        ->overdue();
                    $filters[] = 'Просроченные отключения';
                    break;
                default:
                    $works
                        ->current();
                    $filters[] = 'Активные отключения';
                    break;
            }

            if ( ! empty( $request->get( 'id' ) ) )
            {
                $works
                    ->where( Work::$_table . '.id', '=', $request->get( 'id' ) );
                $filters[] = 'Номер сообщения: ' . $request->get( 'id' );
            }

            if ( ! empty( $request->get( 'category_id' ) ) )
            {
                $works
                    ->where( Work::$_table . '.category_id', '=', $request->get( 'category_id' ) );
                $filters[] = 'Ресурс отключения: ' . Category::find( $request->get( 'category_id' ) )->name;
            }

            if ( ! empty( $request->get( 'composition' ) ) )
            {
                $q = '%' . str_replace( ' ', '%', $request->get( 'composition' ) ) . '%';
                $works
                    ->where( Work::$_table . '.composition', 'like', $q );
                $filters[] = 'Состав работ: ' . $request->get( 'composition' );
            }

            if ( ! empty( $request->get( 'reason' ) ) )
            {
                $q = '%' . str_replace( ' ', '%', $request->get( 'reason' ) ) . '%';
                $works
                    ->where( Work::$_table . '.reason', 'like', $q );
                $filters[] = 'Основание: ' . $request->get( 'reason' );
            }

            if ( ! empty( $request->get( 'begin_from' ) ) )
            {
                $begin_from = Carbon::parse( $request->get( 'begin_from' ) )
                    ->toDateTimeString();
                $works
                    ->where( Work::$_table . '.time_begin', '>=', $begin_from );
                $filters[] = 'Время начала от: ' . $begin_from;
            }

            if ( ! empty( $request->get( 'begin_to' ) ) )
            {
                $begin_to = Carbon::parse( $request->get( 'begin_to' ) )
                    ->toDateTimeString();
                $works
                    ->where( Work::$_table . '.time_begin', '<=', $begin_to );
                $filters[] = 'Время начала до: ' . $begin_to;
            }

            if ( ! empty( $request->get( 'end_from' ) ) )
            {
                $end_from = Carbon::parse( $request->get( 'end_from' ) )
                    ->toDateTimeString();
                $works
                    ->where( function ( $q ) use ( $end_from )
                    {
                        return $q
                            ->where( Work::$_table . '.time_end', '>=', $end_from )
                            ->orWhere( Work::$_table . '.time_end_fact', '>=', $end_from );
                    });
                $filters[] = 'Время окончания от: ' . $end_from;
            }

            if ( ! empty( $request->get( 'end_to' ) ) )
            {
                $end_to = Carbon::parse( $request->get( 'end_to' ) )
                    ->toDateTimeString();
                $works
                    ->where( function ( $q ) use ( $end_to )
                    {
                        return $q
                            ->where( Work::$_table . '.time_end', '<=', $end_to )
                            ->orWhere( Work::$_table . '.time_end_fact', '<=', $end_to );
                    });
                $filters[] = 'Время окончания до: ' . $end_to;
            }

            if ( count( $managements ) )
            {
                $works
                    ->whereIn( Work::$_table . '.management_id', $managements );
                $filters[] = 'УО: ' . Management::whereIn( 'id', $managements )->get()->implode( 'name', ', ' );
            }

            if ( ! empty( $request->get( 'executor_id' ) ) )
            {
                $works
                    ->where( Work::$_table . '.executor_id', '=', $request->get( 'executor_id' ) );
                $filters[] = 'Исполнитель: ' . Executor::find( $request->get( 'executor_id' ) )->name;
            }

            if ( ! empty( $request->get( 'segment_id' ) ) )
            {
                $segment = Segment::find( $request->get( 'segment_id' ) );
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                    {
                        return $buildings
                            ->where( Building::$_table . '.segment_id', '=', $request->get( 'segment_id' ) );
                    } );
                $filters[] = 'Сегмент: ' . $segment->name;
            }

            if ( ! empty( $request->get( 'building_id' ) ) )
            {
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                    {
                        return $buildings
                            ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                    } );
                $building = Building::where( 'id', '=', $request->get( 'building_id' ) )
                    ->pluck( 'name', 'id' );
                $filters[] = 'Здание: ' . $building->first()->name;
            }

			/*
            if ( $request->get( 'export' ) == 'data' && \Auth::user()->can( 'works.export' ) )
            {
                $works = $works->get();
                $data = [];
                foreach ( $works as $work )
                {
                    $data[] = [
                        '#' => $work->id,
                        'Дата и время' => $work->created_at->format( 'd.m.y H:i' ),
                        'Кто сообщил' => $work->who,
                        'Основание' => $work->reason,
                        'Адрес работ' => $work->buildings->implode( 'name', '; ' ),
                        'Категория работ' => $work->category->name,
                        'Исполнитель работ' => $work->management->name,
                        'Состав работ' => $work->composition,
                        'Время начала работ' => Carbon::parse( $work->time_begin )
                            ->format( 'd.m.y H:i' ),
                        'Время окончания работ' => Carbon::parse( $work->time_end )
                            ->format( 'd.m.y H:i' ),
                    ];
                }
				
				$log = Log::create([
					'text' => 'Выгрузил данные по отключениям'
				]);
				$log->save();
				
                \Excel::create( 'Отключения', function ( $excel ) use ( $data )
                {
                    $excel->sheet( 'Отключения', function ( $sheet ) use ( $data )
                    {
                        $sheet->fromArray( $data );
                    } );
                } )
                    ->export( 'xls' );
            }
			*/

            if ( $request->get( 'export' ) == 'report' && \Auth::user()->can( 'works.export' ) )
            {
                $works = $works
                    ->whereHas( 'category', function ( $category )
                    {
                        return $category
                            ->where( 'works', '=', 1 );
                    })
                    ->get();
                $data = [];
                $totals = [
                    'buildings' => 0,
                    'flats'     => 0
                ];
                foreach ( $works as $work )
                {
                    $count_flats = 0;
                    $count_buildings = 0;
                    foreach ( $work->buildings as $building )
                    {
                        $count_flats += $building->room_living_count;
                        $count_buildings ++;
                    }
                    if ( ! isset( $data[ $work->category_id ] ) )
                    {
                        /*if ( $work->is_plan )
                        {
                            $period = $work->category->period_execution_plan;
                        }
                        else
                        {
                            $period = $work->category->period_execution;
                        }
                        if ( $period > 24 )
                        {
                            $period = ceil($period / 24 ) . ' д.';
                        }
                        else
                        {
                            $period = $period . ' ч.';
                        }*/
                        $data[ $work->category_id ] = [
                            'title' => $work->category->name,
                            'color' => $work->category->color,
                            //'period' => $period,
                            'works' => [],
                            'totals' => [
                                'buildings' => 0,
                                'flats'     => 0
                            ]
                        ];
                    }
                    $data[ $work->category_id ][ 'totals' ][ 'buildings' ] += $count_buildings;
                    $data[ $work->category_id ][ 'totals' ][ 'flats' ] += $count_flats;
                    $totals[ 'flats' ] += $count_flats;
                    $totals[ 'buildings' ] += $count_buildings;
                    $data[ $work->category_id ][ 'works' ][ $work->id ] = [
                        'addresses' => [],
                        'count_flats' => $count_flats,
                        'count_buildings' => $count_buildings,
                        'time_begin' => Carbon::parse( $work->time_begin ),
                        'time_end' => Carbon::parse( $work->time_end ),
                        'composition' => $work->composition,
                        'management' => $work->management->name ?? null,
                        'executor_name' => $work->executor->name ?? null,
                        'executor_phone' => $work->executor ? $work->executor->getPhone() : null,
                    ];
                    foreach ( $work->getAddressesGroupBySegment() as $segment )
                    {
                        $data[ $work->category_id ][ 'works' ][ $work->id ][ 'addresses' ][] = $segment[ 0 ] . ' д. ' . implode( ', ', $segment[ 1 ] );
                    }
                }
				
				$log = Log::create([
					'text' => 'Скачал отчет по отключениям'
				]);
				$log->save();
				
                \Excel::create( 'Отчет по отключениям', function ( $excel ) use ( $data, $totals, $filters )
                {
                    $excel->sheet( 'Отчет по отключениям', function ( $sheet ) use ( $data, $totals, $filters )
                    {
                        $sheet
                            ->loadView( 'works.report' )
                            ->with( 'data', $data )
                            ->with( 'totals', $totals )
                            ->with( 'filters', $filters );
                    });

                })->export( 'xls' );
				
            }

            $works = $works
                ->with(
                    'comments',
                    'buildings',
                    'management',
                    'category'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            return view( 'works.parts.list' )
                ->with( 'works', $works );

        }


        $log = Log::create([
            'text' => 'Просмотрел список отключений'
        ]);
        $log->save();

        return view( 'works.index' )
            ->with( 'request', $request );

    }

    public function searchForm ( Request $request )
    {

        if ( ! \Auth::user()->can( 'works.search' ) )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Доступ запрещен' );
        }

        $managements = [];

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        $res = Management
            ::mine()
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableManagements = [];
        foreach ( $res as $r )
        {
            $availableManagements[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        $categories = Category
            ::mine()
            ->where( Category::$_table . '.works', '=', 1 )
            ->orderBy( Category::$_table . '.name' )
            ->pluck( Category::$_table . '.name', Category::$_table . '.id' );

        if ( ! empty( $request->get( 'segment_id' ) ) )
        {
            $segment = Segment::find( $request->get( 'segment_id' ) );
        }

        if ( ! empty( $request->get( 'building_id' ) ) )
        {
            $building = Building::where( 'id', $request->get( 'building_id' ) )->pluck( 'name', 'id' );
        }

        return view( 'works.parts.search' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'managements', $managements )
            ->with( 'providers', $providers )
            ->with( 'categories', $categories )
            ->with( 'building', $building ?? [] )
            ->with( 'segment', $segment ?? [] );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить сообщение' );

        $buildings = new Collection();

        if ( ! empty( \Input::old( 'building_id', [] ) ) )
        {
            $buildings = Building
                ::whereIn( 'id', \Input::old( 'building_id' ) )
                ->get();
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$categories = Category
			::mine()
			->orderBy( Category::$_table . '.name' )
			->pluck( Category::$_table . '.name', Category::$_table . '.id' );

        return view( 'works.create' )
            ->with( 'managements', Management::mine()->orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'providers', $providers )
            ->with( 'buildings', $buildings )
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
            'provider_id'       => 'nullable|integer',
            'category_id'       => 'required|integer',
            'buildings'         => 'required|array',
            'management_id'     => 'required|integer',
            'executor_id'       => 'nullable|integer',
            'executor_name'     => 'nullable|max:255',
            'executor_phone'    => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'comment'           => 'max:255',
            'reason'            => 'required|max:255',
            'date_begin'        => 'required|date_format:d.m.Y',
            'time_begin'        => 'required|date_format:G:i',
            'date_end'          => 'required|date_format:d.m.Y',
            'time_end'          => 'required|date_format:G:i',
            'date_end_fact'     => 'nullable|date_format:d.m.Y',
            'time_end_fact'     => 'nullable|date_format:G:i',
        ];

        $this->validate( $request, $rules );

        \DB::beginTransaction();

        $work = Work::create( $request->all() );

        if ( $work instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $work );
        }

        if ( ! empty( $request->get( 'executor_name' ) ) )
        {
            $executor = Executor::create([
                'management_id'     => $work->management_id,
                'name'              => $request->get( 'executor_name' ),
                'phone'             => $request->get( 'executor_phone' ),
            ]);
            if ( $executor instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $executor );
            }
            $executor->save();
            $work->executor_id = $executor->id;
            $work->save();
        }

        if ( ! empty( $request->comment ) )
        {
            $comment = $work->addComment( $request->comment );
            if ( $comment instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $comment );
            }
        }

        $work->buildings()
            ->sync( $request->get( 'buildings', [] ) );

        \DB::commit();

        \Cache::tags( 'works_counts' )
            ->flush();

        return redirect()
            ->route( 'works.edit', $work->id )
            ->with( 'success', 'Сообщение успешно добавлено' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {

        if ( \Auth::user()->can( 'works.edit' ) )
        {
            return redirect()->route( 'works.edit', $id );
        }

        Title::add( 'Просмотр сообщения' );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
        }

        return view( 'works.show' )
            ->with( 'work', $work );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        if ( ! \Auth::user()->can( 'works.edit' ) )
        {
            return redirect()->route( 'works.show', $id );
        }

        Title::add( 'Редактировать сообщение' );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
        }

        $managements = Management::orderBy( 'name' )->get();

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );
			
		$categories = Category
			::mine()
			->orderBy( Category::$_table . '.name' )
			->pluck( Category::$_table . '.name', Category::$_table . '.id' );

        return view( 'works.edit' )
            ->with( 'work', $work )
            ->with( 'managements', $managements )
            ->with( 'providers', $providers )
            ->with( 'categories', $categories );

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

        $this->validate( $request, Work::$rules );

        if ( ! isset( Work::$categories[ $request->get( 'category_id' ) ] ) )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Некорректная категория' ] );
        }

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Запись не найдена' ] );
        }

        \DB::beginTransaction();

        $res = $work->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        if ( ! empty( $request->get( 'executor_name' ) ) )
        {
            $executor = Executor::create([
                'management_id'     => $work->management_id,
                'name'              => $request->get( 'executor_name' ),
                'phone'             => $request->get( 'executor_phone' ),
            ]);
            if ( $executor instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $executor );
            }
            $executor->save();
            $work->executor_id = $executor->id;
            $work->save();
        }

        $work->buildings()
            ->sync( $request->get( 'buildings', [] ) );

        \DB::commit();

        \Cache::tags( 'works_counts' )
            ->flush();

        return redirect()
            ->back()
            ->with( 'success', 'Сообщение успешно обновлено' );

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

    public function comment ( Request $request, $id )
    {



    }

    public function filter ( Request $request )
    {

        $data = $request->all();

        unset( $data[ '_token' ] );

        foreach ( $data as $key => $val )
        {
            if ( empty( $val ) )
            {
                unset( $data[ $key ] );
            }
        }

        if ( isset( $data[ 'managements' ] ) )
        {
            $data[ 'managements' ] = implode( ',', $data[ 'managements' ] );
        }

        $url = route( 'works.index', $data ) . '#result';

        return redirect()->to( $url );

    }

    public function search ( Request $request )
    {

        $now = Carbon::now()->toDateString();

        $works = Work
            ::whereHas( 'buildings', function ( $buildings ) use ( $request )
            {
                return $buildings
                    ->mine()
                    ->where( Building::$_table. '.id', '=', $request->get( 'building_id' ) );
            })
            ->whereRaw( 'DATE( time_begin ) <= ? AND DATE( time_end ) >= ?', [ $now, $now ] )
            ->orderBy( Work::$_table . '.id', 'desc' )
            ->take( 10 )
            ->get();

        if ( $works->count() )
        {
            return view( 'works.select' )
                ->with( 'works', $works );
        }

    }

}
