<?php

namespace App\Http\Controllers\Operator;

use App\Classes\SegmentChilds;
use App\Classes\Title;
use App\Jobs\SendPush;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\Customer;
use App\Models\Executor;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\TicketManagement;
use App\Models\Type;
use App\Models\Work;
use App\User;
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

    public function index ( Request $request, $show = null )
    {

        if ( $request->ajax() )
        {

            $managements = [];
            $executors = [];

            if ( ! empty( $request->get( 'managements' ) ) )
            {
                $managements = explode( ',', $request->get( 'managements' ) );
            }

            if ( ! empty( $request->get( 'executors' ) ) )
            {
                $executors = explode( ',', $request->get( 'executors' ) );
            }

            $works = Work
                ::mine()
                ->orderBy( Work::$_table . '.id', 'desc' );

            switch ( $request->get( 'show', $show ) )
            {
                case 'overdue':
                    $works
                        ->current()
                        ->overdue();
                    break;
                case 'period':

                    break;
                default:
                    $works
                        ->current();
                    break;
            }

            if ( ! empty( $request->get( 'id' ) ) )
            {
                $works
                    ->where( Work::$_table . '.id', '=', $request->get( 'id' ) );
            }

            if ( ! empty( $request->get( 'category_id' ) ) )
            {
                $works
                    ->where( Work::$_table . '.category_id', '=', $request->get( 'category_id' ) );
            }

            if ( $request->get( 'type_id' ) != '' )
            {
                $works
                    ->where( Work::$_table . '.type_id', '=', $request->get( 'type_id' ) );
            }

            if ( ! empty( $request->get( 'composition' ) ) )
            {
                $q = '%' . str_replace( ' ', '%', $request->get( 'composition' ) ) . '%';
                $works
                    ->where( Work::$_table . '.composition', 'like', $q );
            }

            if ( ! empty( $request->get( 'reason' ) ) )
            {
                $q = '%' . str_replace( ' ', '%', $request->get( 'reason' ) ) . '%';
                $works
                    ->where( Work::$_table . '.reason', 'like', $q );
            }

            if ( ! empty( $request->get( 'begin_from' ) ) )
            {
                $begin_from = Carbon::parse( $request->get( 'begin_from' ) )
                    ->toDateTimeString();
                $works
                    ->where( Work::$_table . '.time_begin', '>=', $begin_from );
            }

            if ( ! empty( $request->get( 'begin_to' ) ) )
            {
                $begin_to = Carbon::parse( $request->get( 'begin_to' ) )
                    ->toDateTimeString();
                $works
                    ->where( Work::$_table . '.time_begin', '<=', $begin_to );
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
            }

            if ( count( $managements ) )
            {
                $works
                    ->whereHas( 'managements', function ( $_managements ) use ( $managements )
                    {
                        return $_managements
                            ->whereIn( Management::$_table . '.id', $managements );
                    });
            }

            if ( count( $executors ) )
            {
                $works
                    ->whereHas( 'executors', function ( $_executors ) use ( $executors )
                    {
                        return $_executors
                            ->whereIn( Executor::$_table . '.id', $executors );
                    });
            }

            if ( ! empty( $request->get( 'segments' ) ) )
            {
                $segments = Segment::whereIn( 'id', $request->get( 'segments' ) )->get();
                if ( $segments->count() )
                {
                    $segmentsIds = [];
                    foreach ( $segments as $segment )
                    {
                        $segmentName = $segment->type->name;
                        if ( $segment->parent )
                        {
                            $segmentName .= ' ' . $segment->parent->name;
                        }
                        $segmentName .= ' ' . $segment->name;
                        $filters[] = 'Сегмент: ' . $segmentName;
                        $segmentChilds = new SegmentChilds( $segment );
                        $segmentsIds += $segmentChilds->ids;
                    }
                    $works
                        ->whereHas( 'buildings', function ( $buildings ) use ( $segmentsIds )
                        {
                            return $buildings
                                ->whereIn( Building::$_table . '.segment_id', $segmentsIds );
                        });
                }
            }

            if ( ! empty( $request->get( 'building_id' ) ) )
            {
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                    {
                        return $buildings
                            ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                    });
            }

            $works = $works
                ->with(
                    'buildings',
                    'buildings.buildingType',
                    'managements',
                    'managements.parent',
                    'category'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            $this->addLog( 'Просмотрел список отключений (стр.' . $request->get( 'page', 1 ) . ')' );

            return view( 'works.parts.list' )
                ->with( 'works', $works );

        }

        if ( \Auth::user()->can( 'tickets.scheduled' ) )
		{
			if ( \Cache::tags( 'tickets.scheduled.now' )->has( 'tickets.scheduled.now.' . \Auth::user()->id ) )
			{
				$scheduledTicketManagements = \Cache::tags( 'tickets.scheduled.now' )->get( 'tickets.scheduled.now.' . \Auth::user()->id );
			}
			else
			{
				$now = Carbon::now()->toDateTimeString();
				$scheduledTicketManagements = TicketManagement
					::mine()
					->where( 'status_code', '=', 'assigned' )
					->where( 'scheduled_begin', '<=', $now )
					->whereDoesntHave( 'ticket', function ( $ticket ) use ( $now )
					{
						return $ticket
							->whereNotNull( 'postponed_to' )
							->where( 'postponed_to', '>', $now );
					})
					->get();
				\Cache::tags( 'tickets.scheduled.now' )->put( 'tickets.scheduled.now.' . \Auth::user()->id, $scheduledTicketManagements, 15 );
			}
		}
		else
		{
			$scheduledTicketManagements = new Collection();
		}

        switch ( $request->get( 'show', $show ) )
        {
            case 'period':
                Title::set( 'Отключения за период' );
                break;
            case 'overdue':
                Title::set( 'Просроченные отключения' );
                break;
        }

        return view( 'works.index' )
            ->with( 'request', $request )
            ->with( 'scheduledTicketManagements', $scheduledTicketManagements );

    }

    public function comments ( Request $request, $id = null )
    {

        if ( $id )
        {
            $work = Work::find( $id );
            return view( 'parts.comments' )
                ->with( 'origin', $work )
                ->with( 'comments', $work->comments );
        }
        else if ( is_array( $request->get( 'ids' ) ) && count( $request->get( 'ids' ) ) )
        {
            $works = Work
                ::whereIn( 'id', $request->get( 'ids' ) )
                ->get();
            $response = [];
            foreach ( $works as $work )
            {
                if ( $work->comments->count() )
                {
                    $response[ $work->id ] = view( 'parts.comments' )
                        ->with( 'origin', $work )
                        ->with( 'comments', $work->comments )
                        ->render();
                }
            }
            return $response;
        }

    }

    public function export ( Request $request )
    {

        $works = Work
            ::mine()
            ->orderBy( Work::$_table . '.id', 'desc' );

        switch ( $request->get( 'show' ) )
        {
            case 'overdue':
                $works
                    ->current()
                    ->overdue();
                break;
        }

        if ( ! empty( $request->get( 'id' ) ) )
        {
            $works
                ->where( Work::$_table . '.id', '=', $request->get( 'id' ) );
        }

        if ( ! empty( $request->get( 'category_id' ) ) )
        {
            $works
                ->where( Work::$_table . '.category_id', '=', $request->get( 'category_id' ) );
        }

        if ( ! empty( $request->get( 'composition' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'composition' ) ) . '%';
            $works
                ->where( Work::$_table . '.composition', 'like', $q );
        }

        if ( ! empty( $request->get( 'reason' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'reason' ) ) . '%';
            $works
                ->where( Work::$_table . '.reason', 'like', $q );
        }

        if ( ! empty( $request->get( 'begin_from' ) ) )
        {
            $begin_from = Carbon::parse( $request->get( 'begin_from' ) )
                ->toDateTimeString();
            $works
                ->where( Work::$_table . '.time_begin', '>=', $begin_from );
        }

        if ( ! empty( $request->get( 'begin_to' ) ) )
        {
            $begin_to = Carbon::parse( $request->get( 'begin_to' ) )
                ->toDateTimeString();
            $works
                ->where( Work::$_table . '.time_begin', '<=', $begin_to );
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
        }

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $works
                ->whereIn( Work::$_table . '.management_id', $request->get( 'managements' ) );
        }

        if ( ! empty( $request->get( 'executor_id' ) ) )
        {
            $works
                ->where( Work::$_table . '.executor_id', '=', $request->get( 'executor_id' ) );
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
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $segmentsIds )
                    {
                        return $buildings
                            ->whereIn( Building::$_table . '.segment_id', $segmentsIds );
                    });
            }
        }

        if ( ! empty( $request->get( 'building_id' ) ) )
        {
            $works
                ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                } );
        }

        $works = $works->get();
        $data = [];
        foreach ( $works as $work )
        {
            $managements = [];
            $executors = [];
            foreach ( $work->managements as $management )
            {
                $management_name = $management->name;
                if ( $management->parent )
                {
                    $management_name = $management->parent->name . ' ' . $management_name;
                }
                $managements[] = $management_name;
            }
            foreach ( $work->executors as $executor )
            {
                $executor_name = $executor->name;
                if ( $executor->phone )
                {
                    $executor_name .= ' (' . $executor->phone . ')';
                }
                $executors[] = $executor_name;
            }
            $segments = $work->getAddressesGroupBySegment();
            if ( count( $segments ) )
            {
                foreach ( $segments as $segment )
                {
                    $address = $segment[ 0 ];
                    $buildings = implode( ', ', $segment[ 1 ] );
                    $data[] = [
                        '#' => $work->id,
                        'Дата и время' => $work->created_at->format( 'd.m.y H:i' ),
                        'Категория работ' => $work->category->name,
                        'Тип отключения' => ( Work::$types[ $work->type_id ] ?? '-' ),
                        'Адрес работ' => $address,
                        'Дома' => $buildings,
                        'Исполнитель работ' => implode( '; ', $managements ),
                        'Ответственный' => implode( '; ', $executors ),
                        'Основание' => $work->reason,
                        'Состав работ' => $work->composition,
                        'Время начала работ' => Carbon::parse( $work->time_begin )
                            ->format( 'd.m.y H:i' ),
                        'Время окончания работ' => Carbon::parse( $work->time_end )
                            ->format( 'd.m.y H:i' ),
                    ];
                }
            }
            else
            {
                foreach ( $work->buildings as $building )
                {
                    $data[] = [
                        '#' => $work->id,
                        'Дата и время' => $work->created_at->format( 'd.m.y H:i' ),
                        'Категория работ' => $work->category->name,
                        'Тип отключения' => ( Work::$types[ $work->type_id ] ?? '-' ),
                        'Адрес работ' => $building->name,
                        'Дома' => $building->number,
                        'Исполнитель работ' => implode( '; ', $managements ),
                        'Ответственный' => implode( '; ', $executors ),
                        'Основание' => $work->reason,
                        'Состав работ' => $work->composition,
                        'Время начала работ' => Carbon::parse( $work->time_begin )
                            ->format( 'd.m.y H:i' ),
                        'Время окончания работ' => Carbon::parse( $work->time_end )
                            ->format( 'd.m.y H:i' ),
                    ];
                }
            }

        }

        $this->addLog( 'Выгрузил данные по отключениям' );

        \Excel::create( 'Отключения', function ( $excel ) use ( $data )
        {
            $excel->sheet( 'Отключения', function ( $sheet ) use ( $data )
            {
                $sheet->fromArray( $data );
            } );
        } )
            ->export( 'xls' );

        die;

    }

    public function report ( Request $request, $show = null )
    {

        $managements = $request->get( 'managements' );
        $filters = [];

        $works = Work
            ::mine()
            ->orderBy( Work::$_table . '.id', 'desc' );

        switch ( $request->get( 'show' ) )
        {
            case 'all':
                $filters[] = 'Отключения за все время';
                break;
            case 'period':
                $filters[] = 'Отключения за период';
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
            $filters[] = 'Ресурс отключения: ' . Type::find( $request->get( 'category_id' ) )->name;
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
        if ( $managements && count( $managements ) )
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

        if ( ! empty( $request->get( 'segments' ) ) )
        {
            $segments = Segment::whereIn( 'id', $request->get( 'segments' ) )->get();
            if ( $segments->count() )
            {
                $segmentsIds = [];
                foreach ( $segments as $segment )
                {
                    $segmentName = $segment->type->name;
                    if ( $segment->parent )
                    {
                        $segmentName .= ' ' . $segment->parent->name;
                    }
                    $segmentName .= ' ' . $segment->name;
                    $filters[] = 'Сегмент: ' . $segmentName;
                    $segmentChilds = new SegmentChilds( $segment );
                    $segmentsIds += $segmentChilds->ids;
                }
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $segmentsIds )
                    {
                        return $buildings
                            ->whereIn( Building::$_table . '.segment_id', $segmentsIds );
                    });
            }
        }

        if ( ! empty( $request->get( 'building_id' ) ) )
        {
            $works
                ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                } );
            $building = Building::find( $request->get( 'building_id' ) );
            $filters[] = 'Здание: ' . $building->name;
        }


        $categories = Type
            ::mine()
            ->where( 'works', '=', 1 )
            ->orderBy( 'sort' )
            ->get();
        $works = $works
            ->whereIn( Work::$_table . '.category_id', $categories->pluck( 'id' ) )
            ->get();
        $data = [];
        $totals = [
            'buildings' => 0,
            'flats'     => 0
        ];
        foreach ( $categories as $category )
        {
            $data[ $category->id ] = [
                'list' => [],
                'totals' => [
                    'buildings' => 0,
                    'flats'     => 0
                ]
            ];
        }
        foreach ( $works as $work )
        {
            $count_flats = 0;
            $count_buildings = 0;
            foreach ( $work->buildings as $building )
            {
                $count_flats += $building->room_living_count;
                $count_buildings ++;
            }
            $data[ $work->category_id ][ 'totals' ][ 'buildings' ] += $count_buildings;
            $data[ $work->category_id ][ 'totals' ][ 'flats' ] += $count_flats;
            $totals[ 'flats' ] += $count_flats;
            $totals[ 'buildings' ] += $count_buildings;
            $executors = '';
            foreach ( $work->executors as $executor )
            {
                $executor_name = $executor->name;
                if ( $executor->getPhone() )
                {
                    $executor_name .= ' (' . $executor->getPhone() . ')';
                }
                $executor_name .= ' ';
                $executors .= $executor_name;
            }
            $data[ $work->category_id ][ 'list' ][ $work->id ] = [
                'addresses' => [],
                'count_flats' => $count_flats,
                'count_buildings' => $count_buildings,
                'time_begin' => Carbon::parse( $work->time_begin ),
                'time_end' => Carbon::parse( $work->time_end ),
                'composition' => $work->composition,
                'managements' => $work->managements->implode( 'name', '; ' ),
                'executors' => $executors,
                'type' => $work->type_id ? Work::$types[ $work->type_id ] : null,
            ];
            if ( isset( Work::$deadline_units[ $work->deadline_unit ] ) )
            {
                $data[ $work->category_id ][ 'list' ][ $work->id ][ 'deadline' ] = $work->deadline;
                $data[ $work->category_id ][ 'list' ][ $work->id ][ 'deadline_unit' ] = mb_substr( Work::$deadline_units[ $work->deadline_unit ], 0, 1 );
            }
            foreach ( $work->getAddressesGroupBySegment() as $segment )
            {
                $data[ $work->category_id ][ 'list' ][ $work->id ][ 'addresses' ][] = $segment[ 0 ] . ' д. ' . implode( ', ', $segment[ 1 ] );
            }
        }

        $log = Log::create([
            'text' => 'Скачал отчет по отключениям'
        ]);
        $log->save();

        \Excel::create( 'Отчет по отключениям', function ( $excel ) use ( $categories, $data, $totals, $filters )
        {
            $excel->sheet( 'Отчет по отключениям', function ( $sheet ) use ( $categories, $data, $totals, $filters )
            {
                $sheet
                    ->loadView( 'works.report' )
                    ->with( 'categories', $categories )
                    ->with( 'data', $data )
                    ->with( 'totals', $totals )
                    ->with( 'filters', $filters );
            });

        })->export( 'xls' );

        die;

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

        $categories = Type
            ::mine()
            ->where( Type::$_table . '.works', '=', 1 )
            ->orderBy( Type::$_table . '.name' )
            ->pluck( Type::$_table . '.name', Type::$_table . '.id' );

        return view( 'works.parts.search' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'managements', $managements )
            ->with( 'providers', $providers )
            ->with( 'categories', $categories );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ( Request $request )
    {

        Title::add( 'Добавить сообщение' );

        $buildings = new Collection();

        if ( ! empty( \Input::old( 'building_id', [] ) ) )
        {
            $buildings = Building
                ::whereIn( 'id', $request->old( 'building_id' ) )
                ->get();
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        $res = Type
			::mine()
            ->where( 'works', '=', 1 )
            ->where( 'provider_id', '=', $provider_id )
            ->get()
			->sortBy( 'name' );
        $availableCategories = [];
        foreach ( $res as $r )
        {
            $availableCategories[ $r->id ] = $r->name;
        }

        $res = Management
            ::mine()
            ->where( 'provider_id', '=', $provider_id )
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableManagements = [];
        foreach ( $res as $r )
        {
            $availableManagements[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        return view( 'works.create' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'providers', $providers )
            ->with( 'buildings', $buildings )
            ->with( 'availableCategories', $availableCategories );

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
            'managements'       => 'required|array',
            'executors'         => 'required|array',
            'executor_name'     => 'nullable|max:255',
            'executor_phone'    => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'comment'           => 'max:255',
            'reason'            => 'max:255',
            'composition'       => 'max:2000',
            'date_begin'        => 'required|date_format:d.m.Y',
            'time_begin'        => 'required|date_format:G:i',
            'date_end'          => 'required|date_format:d.m.Y',
            'time_end'          => 'required|date_format:G:i',
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

        $work->managements()
            ->sync( $request->get( 'managements', [] ) );

        $work->executors()
            ->sync( $request->get( 'executors', [] ) );

        $work->buildings()
            ->sync( $request->get( 'buildings', [] ) );

        \DB::commit();

        \Cache::tags( 'works_counts' )
            ->flush();

        $users = User
            ::whereNotNull( 'push_id' )
            ->where( 'active', '=', 1 )
            ->whereHas( 'customer', function ( $customer ) use ( $request )
            {
                return $customer
                    ->where( function ( $q ) use ( $request )
                    {
                        return $q
                            ->whereIn( Customer::$_table . '.actual_building_id', $request->get( 'buildings', [] ) )
                            ->orWhereHas( 'buildings', function ( $buildings ) use ( $request )
                            {
                                return $buildings
                                    ->whereIn( Building::$_table . '.id', $request->get( 'buildings', [] ) );
                            });
                    });
            })
            ->get();

        foreach ( $users as $user )
        {
            $this->dispatch( new SendPush( config( 'push.keys.lk' ), $user->push_id, 'Новое отключение', 'Новое отключение', 'work', $work->id ) );
        }

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

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
        }

        Title::add( 'Отключение #' . $work->id );

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

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
        }

        if ( $work->time_end_fact )
        {
            return redirect()->route( 'works.show', $id );
        }

        Title::add( 'Редактировать отключение #' . $work->id );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        $availableCategories = Type
            ::mine()
            ->where( 'works', '=', 1 )
            ->orderBy( Type::$_table . '.name' )
            ->pluck( Type::$_table . '.name', Type::$_table . '.id' );

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

        return view( 'works.edit' )
            ->with( 'work', $work )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'providers', $providers )
            ->with( 'availableCategories', $availableCategories );

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

        $rules = [
            'provider_id'       => 'nullable|integer',
            'category_id'       => 'required|integer',
            'buildings'         => 'required|array',
            'managements'       => 'required|array',
            'executors'         => 'required|array',
            'executor_name'     => 'nullable|max:255',
            'executor_phone'    => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'comment'           => 'max:255',
            'reason'            => 'max:255',
            'composition'       => 'max:2000',
            'date_begin'        => 'required|date_format:d.m.Y',
            'time_begin'        => 'required|date_format:G:i',
            'date_end'          => 'required|date_format:d.m.Y',
            'time_end'          => 'required|date_format:G:i',
        ];

        $this->validate( $request, $rules );

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

        if ( $request->get( 'closed' ) == 1 )
        {
            $work->time_end_fact = Carbon::now()->toDateTimeString();
            $work->save();
        }

        $work->managements()
            ->sync( $request->get( 'managements', [] ) );

        $work->executors()
            ->sync( $request->get( 'executors', [] ) );

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
            if ( $val == '' )
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

    public function buildingsSearch ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );
        $managements_ids = $request->get( 'managements', [] );

        $buildings = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->leftJoin( BuildingType::$_table, BuildingType::$_table . '.id', '=', Building::$_table . '.building_type_id' )
            ->select(
                Building::$_table . '.id',
                \DB::raw( 'CONCAT_WS( \' \', ' . Building::$_table . '.name, CONCAT( \'(\', ' . BuildingType::$_table . '.name, \')\' ) ) AS text' )
            )
            ->whereHas( 'managements', function ( $managements ) use ( $managements_ids )
            {
                return $managements
                    ->whereIn( Management::$_table . '.id', $managements_ids );
            })
            ->having( 'text', 'like', $s )
            ->orderBy( 'text' );

        if ( ! empty( $provider_id ) )
        {
            $buildings
                ->where( Building::$_table . '.provider_id', '=', $provider_id );
        }

        $buildings = $buildings
            ->get();

        return $buildings;

    }

}
