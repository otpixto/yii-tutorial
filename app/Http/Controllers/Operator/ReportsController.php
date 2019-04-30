<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Asterisk\Cdr;
use App\Models\Executor;
use App\Models\Log;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\Models\Provider;
use App\Models\TypeGroup;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Отчеты' );
    }

    public function index ()
    {

    }

    public function types_groups ( Request $request )
    {

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $managements_ids = $request->get( 'managements_ids', [] );

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

        $data = [];
        $title = 'Отчет по видам работ по заявкам ЕДС, поступившим в период с ' . $date_from->format( 'd.m.Y H:i' ) . ' по ' . $date_to->format( 'd.m.Y H:i' );

        if ( count( $managements_ids ) )
        {

            $groups = TypeGroup
                ::orderBy( 'name' )
                ->get();

            $managements = Management
                ::mine()
                ->whereIn( 'id', $managements_ids )
                ->orderBy( 'name' )
                ->get();

            foreach ( $managements as $management )
            {
                $data[ $management->name ] = [
                    'totals' => [
                        'completed'             => 0,
                        'in_process'            => 0,
                        'waiting'               => 0,
                        'total'                 => 0,
                        'completed_percent'     => 0,
                    ],
                    'groups' => []
                ];
                foreach ( $groups as $group )
                {
                    $ticketManagements = TicketManagement
                        ::whereHas( 'management', function ( $m ) use ( $management )
                        {
                            return $m
                                ->mine()
                                ->where( 'id', '=', $management->id )
                                ->orWhere( 'parent_id', '=', $management->id );
                        })
                        ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                        ->whereHas( 'ticket', function ( $ticket ) use ( $group )
                        {
                            return $ticket
                                ->whereHas( 'type', function ( $type ) use ( $group )
                                {
                                    return $type
                                        ->where( 'group_id', '=', $group->id );
                                });
                        })
                        ->get();
                    $completed = $ticketManagements
                        ->filter( function ( $item )
                        {
                            return in_array( $item->status_code, [ 'closed_with_confirm', 'closed_without_confirm', 'confirmation_operator', 'confirmation_client', 'cancel', 'not_verified', 'completed_with_act', 'completed_without_act' ] );
                        })
                        ->count();
                    $in_process = $ticketManagements
                        ->filter( function ( $item )
                        {
                            return in_array( $item->status_code, [ 'transferred', 'transferred_again', 'accepted', 'assigned', 'in_process' ] );
                        })
                        ->count();
                    $waiting = $ticketManagements
                        ->filter( function ( $item )
                        {
                            return $item->status_code == 'waiting';
                        })
                        ->count();
                    $total = $completed + $in_process + $waiting;
                    $completed_percent = $total > 0 ? ceil( $completed / $total * 100 ) : 0;
                    $data[ $management->name ][ 'groups' ][ $group->name ] = compact( 'completed', 'in_process', 'waiting', 'total', 'completed_percent' );
                    $data[ $management->name ][ 'totals' ][ 'completed' ] += $completed;
                    $data[ $management->name ][ 'totals' ][ 'in_process' ] += $in_process;
                    $data[ $management->name ][ 'totals' ][ 'waiting' ] += $waiting;
                    $data[ $management->name ][ 'totals' ][ 'total' ] += $total;
                }
                $data[ $management->name ][ 'totals' ][ 'completed_percent' ] = $data[ $management->name ][ 'totals' ][ 'total' ] > 0 ? ceil( $data[ $management->name ][ 'totals' ][ 'completed' ] / $data[ $management->name ][ 'totals' ][ 'total' ] * 100 ) : 0;
            }

            if ( $request->get( 'export', 0 ) == 1 && \Auth::user()->can( 'reports.export' ) )
            {
                $export_data = [];
                foreach ( $data as $management_name => $row )
                {
                    foreach ( $row[ 'groups' ] as $group_name => $count )
                    {
                        $export_data[] = [
                            'УО'                        => $management_name,
                            'Вид работы'                => $group_name,
                            'Выполнено'                 => $count[ 'completed' ] ?: (string) $count[ 'completed' ],
                            'В работе'                  => $count[ 'in_process' ] ?: (string) $count[ 'in_process' ],
                            'Отложено'                  => $count[ 'waiting' ] ?: (string) $count[ 'waiting' ],
                            'Итого'                     => $count[ 'total' ] ?: (string) $count[ 'total' ],
                            'Процент выполнения'        => $count[ 'completed_percent' ] ?: (string) $count[ 'completed_percent' ],
                        ];
                    }
                }
                $this->addLog( 'Выгрузил отчет "' . $title . '"' );
                \Excel::create( $title, function ( $excel ) use ( $export_data )
                {
                    $excel->sheet( 'Отчет', function ( $sheet ) use ( $export_data )
                    {
                        $sheet->fromArray( $export_data );
                    } );
                } )
                    ->export( 'xls' );
            }

            $this->addLog( 'Просмотрел отчет "' . $title . '"' );

        }

        return view( 'reports.types_groups' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'managements_ids', $managements_ids )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'data', $data )
            ->with( 'title', $title );

    }
	
	public function totals ( Request $request )
    {
		
		$date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
		$management_id = $request->get( 'management_id' );
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );
		
		$title = 'Сформировать Справку ЕДС ЖКХ';
		
		Title::add( $title );
		
		$availableManagements = Management
			::mine()
			->whereNull( 'parent_id' )
			->whereHas( 'childs' )
			->where( 'provider_id', '=', $provider_id )
			->orderBy( 'name' )
			->pluck( 'name', 'id' );
		
		return view( 'reports.totals' )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'management_id', $management_id )
            ->with( 'provider_id', $provider_id )
            ->with( 'providers', $providers )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );
		
    }

    public function executors ( Request $request )
    {

        Title::add( 'Отчет по исполнителю' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $management_id = $request->get( 'management_id' );
        $executor_id = $request->get( 'executor_id' );
        $rate_from = $request->get( 'rate_from', 1 );
        $rate_to = $request->get( 'rate_to', 5 );
        $status_code = $request->get( 'status_code' );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
			->where( 'provider_id', '=', $provider_id )
            ->get()
            ->sortBy( 'name' );

        $ticketManagements = null;
        $management = null;
        $executors = null;
        $executor = null;

        if ( $management_id )
        {
            $ticketManagements = TicketManagement
                ::mine()
                ->where( function ( $q ) use ( $rate_from, $rate_to )
                {
                    return $q
                        ->whereNull( 'rate' )
                        ->orWhereBetween( 'rate', [ $rate_from, $rate_to ] );
                })
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->whereIn( 'management_id', $availableManagements->pluck( 'id' ) )
                ->where( 'management_id', '=', $management_id );
            $management = $availableManagements->find( $management_id );
            $executors = Executor
                ::where( 'management_id', '=', $management_id )
                ->get();
            if ( $executor_id )
            {
                $ticketManagements
                    ->where( 'executor_id', '=', $executor_id );
                $executor = $executors->find( $executor_id );
            }
        }

        if ( $status_code )
        {
            $ticketManagements
                ->where( 'status_code', '=', $status_code );
        }

        if ( $request->get( 'export', 0 ) == '1' && \Auth::user()->can( 'reports.export' ) )
        {
            $ticketManagements = $ticketManagements->get();
            $data = [];
            foreach ( $ticketManagements as $ticketManagement )
            {
                $data[] = [
                    'Номер заявки' => $ticketManagement->ticket->id,
                    'Дата создания' => $ticketManagement->created_at->format( 'd.m.Y H:i' ),
                    'Адрес заявки' => $ticketManagement->ticket->getAddress(),
                    'Классификатор' => $ticketManagement->ticket->type->name,
                    'Выполненные работы' => $ticketManagement->services->implode( 'name', '; ' ),
                    'Статус заявки' => $ticketManagement->status_name,
                    'Дата выполнения' => $ticketManagement->ticket->completed_at ? $ticketManagement->ticket->completed_at->format( 'd.m.Y H:i' ) : '',
                    'Оценка' => $ticketManagement->rate,
                ];
            }

            $this->addLog( 'Выгрузил отчет по исполнителям' );

            \Excel::create( 'Отчет по исполнителям', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'Отчет по исполнителям', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                } );
            } )
                ->export( 'xls' );

            die;

        }
        else if ( $ticketManagements )
        {
            $ticketManagements = $ticketManagements
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );
        }

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        $availableStatuses = \Auth::user()->getAvailableStatuses( 'show', true, true );
        unset( $availableStatuses[ 'draft' ] );

        $this->addLog( 'Просмотрел отчет по исполнителям' );

        return view( 'reports.executors' )
            ->with( 'ticketManagements', $ticketManagements ?? null )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'availableStatuses', $availableStatuses )
            ->with( 'management', $management ?? null )
            ->with( 'executors', $executors ?? null )
            ->with( 'executor', $executor ?? null )
            ->with( 'management_id', $management_id )
            ->with( 'executor_id', $executor_id )
			->with( 'providers', $providers )
            ->with( 'provider_id', $provider_id )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'status_code', $status_code );

    }

    public function rates ( Request $request )
    {

        Title::add( 'Статистика оценок' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $managements_ids = $request->get( 'managements_ids', [] );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        $data = [
            'total' => 0,
            'average' => 0,
            'rate-1' => 0,
            'rate-2' => 0,
            'rate-3' => 0,
            'rate-4' => 0,
            'rate-5' => 0
        ];

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
			->where( 'provider_id', '=', $provider_id )
            ->get()
            ->sortBy( 'name' );

        $managements = null;

        if ( count( $managements_ids ) )
        {

            $managements = $availableManagements
                ->whereIn( 'id', $managements_ids );

            foreach ( $managements as $management )
            {

                if ( ! isset( $data[ $management->id ] ) )
                {
                    $data[ $management->id ] = [
                        'total' => 0,
                        'average' => 0,
                        'rate-1' => 0,
                        'rate-2' => 0,
                        'rate-3' => 0,
                        'rate-4' => 0,
                        'rate-5' => 0
                    ];
                }

                $ticketManagements = $management
                    ->tickets()
                    ->whereNotIn( 'status_code', [ 'draft' ] )
                    ->whereNotNull( 'rate' )
                    ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                    ->get();

                foreach ( $ticketManagements as $ticketManagement )
                {

                    $data[ 'total' ] ++;
                    $data[ $ticketManagement->management_id ][ 'total' ] ++;
                    $data[ $ticketManagement->management_id ][ 'rate-' . $ticketManagement->rate ] ++;
                    $data[ 'rate-' . $ticketManagement->rate ] ++;

                }

            }

            $s = 0;
            $c = 0;
            for ( $i = 1; $i <= 5; $i ++ )
            {
                if ( $data[ 'rate-' . $i ] )
                {
                    $s += $data[ 'rate-' . $i ] * $i;
                    $c += $data[ 'rate-' . $i ];
                }
            }

            if ( $c )
            {
                $data[ 'average' ] = number_format($s / $c, 1, '.', '' );
            }

            foreach ( $managements as $management )
            {
                $s = 0;
                $c = 0;
                for ( $i = 1; $i <= 5; $i ++ )
                {
                    if ( $data[ $management->id ][ 'rate-' . $i ] )
                    {
                        $s += $data[ $management->id ][ 'rate-' . $i ] * $i;
                        $c += $data[ $management->id ][ 'rate-' . $i ];
                    }
                }
                if ( $c )
                {
                    $data[ $management->id ][ 'average' ] = number_format($s / $c, 1, '.', '' );
                }
            }

            if ( $request->has( 'export' ) && \Auth::user()->can( 'reports.export' ) )
            {
                $print_data = [];
                foreach ( $managements as $management )
                {
                    $print_data[] = [
                        'Нименование ЭО'                => $management->name,
                        'Всего оценок'                  => $data[ $management->id ][ 'total' ],
                        '1 балл'                        => $data[ $management->id ][ 'rate-1' ],
                        '2 балла'                       => $data[ $management->id ][ 'rate-2' ],
                        '3 балла'                       => $data[ $management->id ][ 'rate-3' ],
                        '4 балла'                       => $data[ $management->id ][ 'rate-4' ],
                        '5 баллов'                      => $data[ $management->id ][ 'rate-5' ],
                        'Средний балл'                  => $data[ $management->id ][ 'average' ],
                    ];
                }
                \Excel::create( Title::get(), function ( $excel ) use ( $print_data )
                {
                    $excel->sheet( Title::get(), function ( $sheet ) use ( $print_data )
                    {
                        $sheet->fromArray( $print_data );
                    });
                })->export( 'xls' );
            }

        }

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        $this->addLog( 'Просмотрел отчет по оценкам' );

        return view( 'reports.rates' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'managements_ids', $managements_ids )
            ->with( 'availableManagements', $availableManagements )
			->with( 'providers', $providers )
            ->with( 'provider_id', $provider_id )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function addresses ( Request $request )
    {

        Title::add( 'Отчет по адресу' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $building_id = $request->get( 'building_id' );
        $building = null;
        $ticketManagements = null;

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        if ( $building_id )
        {
            $building = Building::where( 'id', '=', $building_id )->pluck( 'name', 'id' );
            $ticketManagements = TicketManagement
                ::mine()
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->whereHas( 'ticket', function ( $ticket ) use ( $building_id, $provider_id )
                {
                    return $ticket
                        ->where( 'building_id', '=', $building_id )
                        ->where( 'provider_id', '=', $provider_id );
                })
                ->get();
        }

        if ( $request->get( 'export' ) == '1' && \Auth::user()->can( 'reports.export' ) )
        {
            $data = [];
            foreach ( $ticketManagements as $ticketManagement )
            {
                $management_name = $ticketManagement->management->name;
                if ( $ticketManagement->management->parent )
                {
                    $management_name = $ticketManagement->management->parent->name . ' ' . $management_name;
                }
                $data[] = [
                    'Номер заявки' => $ticketManagement->ticket->id,
                    'Дата создания' => $ticketManagement->created_at->format( 'd.m.Y H:i' ),
                    'Адрес заявки' => $ticketManagement->ticket->getAddress(),
                    'Классификатор' => $ticketManagement->ticket->type->name,
                    'Выполненные работы' => $ticketManagement->services->implode( 'name', '; ' ),
                    'Статус заявки' => $ticketManagement->status_name,
                    'Дата выполнения' => $ticketManagement->ticket->completed_at ? $ticketManagement->ticket->completed_at->format( 'd.m.Y H:i' ) : '',
                    'Зона' => $management_name,
                ];
            }

            $log = Log::create([
                'text' => 'Выгрузил отчет по адресу'
            ]);
            $log->save();

            \Excel::create( 'Отчет по адресу', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'Отчет по адресу', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                } );
            } )
                ->export( 'xls' );

            die;

        }

        $this->addLog( 'Просмотрел отчет по адресу' );
						
        return view( 'reports.addresses' )
            ->with( 'ticketManagements', $ticketManagements )
			->with( 'providers', $providers )
            ->with( 'provider_id', $provider_id )
            ->with( 'building_id', $building_id )
            ->with( 'building', $building )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function tickets ( Request $request )
    {

        Title::add( 'Статистика заявок' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $managements_ids = $request->get( 'managements_ids', [] );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
            ->where( 'provider_id', '=', $provider_id )
            ->get()
            ->sortBy( 'name' );

		if ( count( $managements_ids ) )
        {

            $data = [
                'total' => 0,
                'closed' => 0,
                'not_verified' => 0,
                'canceled' => 0,
                'closed_with_confirm' => 0,
                'closed_without_confirm' => 0
            ];

            $managements = $availableManagements
                ->whereIn( 'id', $managements_ids );

            foreach ( $managements as $management )
            {

                $data[ $management->id ] = [
                    'name' => $management->name,
                    'total' => 0,
                    'closed' => 0,
                    'not_verified' => 0,
                    'canceled' => 0,
                    'closed_with_confirm' => 0,
                    'closed_without_confirm' => 0
                ];

                $ticketManagements = $management
                    ->tickets()
                    ->whereNotIn( 'status_code', [ 'draft' ] )
                    ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                    ->get();

                foreach ( $ticketManagements as $ticketManagement )
                {

                    $data[ 'total' ] ++;
                    $data[ $ticketManagement->management_id ][ 'total' ] ++;
                    switch ( $ticketManagement->status_code )
                    {
                        case 'closed_with_confirm':
                            $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                            $data[ $ticketManagement->management_id ][ 'closed_with_confirm' ] ++;
                            $data[ 'closed' ] ++;
                            $data[ 'closed_with_confirm' ] ++;
                            break;
                        case 'closed_without_confirm':
                            $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                            $data[ $ticketManagement->management_id ][ 'closed_without_confirm' ] ++;
                            $data[ 'closed' ] ++;
                            $data[ 'closed_without_confirm' ] ++;
                            break;
                        case 'not_verified':
                            $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                            $data[ $ticketManagement->management_id ][ 'not_verified' ] ++;
                            $data[ 'closed' ] ++;
                            $data[ 'not_verified' ] ++;
                            break;
                        case 'cancel':
                            $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                            $data[ $ticketManagement->management_id ][ 'canceled' ] ++;
                            $data[ 'closed' ] ++;
                            $data[ 'canceled' ] ++;
                            break;

                    }

                }

            }

            if ( $request->has( 'export' ) && \Auth::user()->can( 'reports.export' ) )
            {
                $print_data = [];
                foreach ( $managements as $management )
                {
                    $print_data[] = [
                        'Нименование ЭО'                => $management->name,
                        'Поступило заявок'              => $data[ $management->id ][ 'total' ],
                        'Всего закрыто заявок'          => $data[ $management->id ][ 'closed' ],
                        'Отменено Заявителем'           => $data[ $management->id ][ 'canceled' ],
                        'Проблема не подтверждена'      => $data[ $management->id ][ 'not_verified' ],
                        'Закрыто c подтверждением'      => $data[ $management->id ][ 'closed_with_confirm' ],
                        'Закрыто без подтверждения'     => $data[ $management->id ][ 'closed_without_confirm' ],
                        '% закрытых заявок'             => $data[ $management->id ][ 'total' ] ? ceil( $data[ $management->id ][ 'closed' ] * 100 / $data[ $management->id ][ 'total' ] ) : 0,
                    ];
                }
                \Excel::create( Title::get(), function ( $excel ) use ( $print_data )
                {
                    $excel->sheet( Title::get(), function ( $sheet ) use ( $print_data )
                    {
                        $sheet->fromArray( $print_data );
                    });
                })->export( 'xls' );
            }

        }

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        $this->addLog( 'Просмотрел отчет по заявкам' );

        return view( 'reports.tickets' )
            ->with( 'data', $data ?? null )
            ->with( 'managements', $managements ?? null )
            ->with( 'availableManagements', $availableManagements )
			->with( 'providers', $providers )
            ->with( 'provider_id', $provider_id )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'managements_ids', $managements_ids );

    }

    public function operators ( Request $request )
    {

        Title::add( 'Статистика по операторам' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $operator_id = $request->get( 'operator_id', null );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $res = Cdr
            ::mine()
            ->answered()
            ->whereBetween( 'calldate', [ $date_from->toDateTimeString(), $date_to->toDateTimeString() ] )
            ->groupBy( 'uniqueid' )
            ->get();

        $data = [];
        $totals = [
            'incoming' => [
                'calls' => 0,
                'duration' => 0,
            ],
            'outgoing' => [
                'calls' => 0,
                'duration' => 0,
            ],
            'tickets' => 0,
        ];

        $one_day = false;

        if ( $date_from->toDateString() == $date_to->toDateString() )
        {
            $format = 'Hч.';
            $one_day = true;
        }
        else
        {
            $format = 'd.m.Y';
        }

        foreach ( $res as $r )
        {
            $date = date( $format, strtotime( $r->calldate ) );
            if ( ! isset( $data[ $date ] ) )
            {
                $data[ $date ] = [
                    'incoming' => [
                        'calls' => 0,
                        'duration' => 0,
                    ],
                    'outgoing' => [
                        'calls' => 0,
                        'duration' => 0,
                    ],
                    'tickets' => 0,
                ];
            }
            if ( $operator_id && ( ! $r->getOperator() || $r->getOperator()->id != $operator_id ) )
            {
                continue;
            }
            if ( $r->getContext() == 'incoming' )
            {
                $data[ $date ][ 'incoming' ][ 'calls' ] ++;
                $data[ $date ][ 'incoming' ][ 'duration' ] += $r->duration;
                $totals[ 'incoming' ][ 'calls' ] ++;
                $totals[ 'incoming' ][ 'duration' ] += $r->duration;
            }
            else if ( $r->getContext() == 'outgoing' )
            {
                $data[ $date ][ 'outgoing' ][ 'calls' ] ++;
                $data[ $date ][ 'outgoing' ][ 'duration' ] += $r->duration;
                $totals[ 'outgoing' ][ 'calls' ] ++;
                $totals[ 'outgoing' ][ 'duration' ] += $r->duration;
            }
        }

        if ( $one_day )
        {
            /*
            for ( $i = 0; $i <= 23; $i ++ )
            {
                $date = mb_substr( '0' . $i . 'ч.', -4 );
                if ( ! isset( $data[ $date ] ) )
                {
                    $data[ $date ] = [
                        'calls' => 0,
                        'duration' => 0,
                        'tickets' => 0,
                    ];
                }
            }
            */
        }
        else
        {
            $current_date = Carbon::parse( $date_from );
            while ( $current_date->format( 'd.m.Y' ) != $date_to->format( 'd.m.Y' ) )
            {
                $date = $current_date->format( 'd.m.Y' );
                if ( ! isset( $data[ $date ] ) )
                {
                    $data[ $date ] = [
                        'incoming' => [
                            'calls' => 0,
                            'duration' => 0,
                        ],
                        'outgoing' => [
                            'calls' => 0,
                            'duration' => 0,
                        ],
                        'tickets' => 0,
                    ];
                }
                $current_date = $current_date->addDay();
            }
        }

        $res = Ticket
            ::mine()
            ->whereBetween( 'created_at', [ $date_from->toDateTimeString(), $date_to->toDateTimeString() ] );

        if ( $operator_id )
        {
            $res
                ->where( 'author_id', '=', $operator_id );
        }

        $res = $res->get();

        foreach ( $res as $r )
        {
            $date = $r->created_at->format( $format );
            if ( ! isset( $data[ $date ] ) )
            {
                $data[ $date ] = [
                    'incoming' => [
                        'calls' => 0,
                        'duration' => 0,
                    ],
                    'outgoing' => [
                        'calls' => 0,
                        'duration' => 0,
                    ],
                    'tickets' => 0,
                ];
            }
            $data[ $date ][ 'tickets' ] ++;
            $totals[ 'tickets' ] ++;
        }

        uksort( $data, function ( $a, $b ) use ( $one_day )
        {
            if ( $one_day )
            {
                return strcmp( $a, $b );
            }
            else
            {
                return strtotime( $a ) > strtotime( $b );
            }
        });

        if ( \Cache::tags( [ 'users', 'reports' ] )->has( 'operators' ) )
        {
            $availableOperators = \Cache::tags( [ 'users', 'reports' ] )->get( 'operators' );
        }
        else
        {
            $res = User::role( 'operator' )->get();
            $availableOperators = [];
            foreach ( $res as $r )
            {
                $availableOperators[ $r->id ] = $r->getName();
            }
            asort( $availableOperators );
            \Cache::tags( [ 'users', 'reports' ] )->put( 'operators', $availableOperators, \Config::get( 'cache.time' ) );
        }

        $this->addLog( 'Просмотрел отчет по операторам' );

        return view( 'reports.operators' )
            ->with( 'data', $data )
            ->with( 'totals', $totals )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'availableOperators', $availableOperators )
            ->with( 'operator_id', $operator_id );

    }

    public function types ( Request $request )
    {

        Title::add( 'Статистика по категориям' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $managements_ids = $request->get( 'managements_ids', [] );
		
		$providers = Provider
			::mine()
			->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );
			
		$provider_id = $request->get( 'provider_id', $providers->keys()->first() );

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
            ->where( 'provider_id', '=', $provider_id )
            ->get()
            ->sortBy( 'name' );

		if ( count( $managements_ids ) )
        {

            $categories = Type
                ::mine()
                ->whereNull( 'parent_id' )
                ->orderBy( 'name' )
                ->where( 'provider_id', '=', $provider_id )
                ->get();

            $managements = $availableManagements
                ->whereIn( 'id', $managements_ids );

            $data = [
                'total' => 0,
                'closed' => 0,
                'percent' => 0,
                'categories' => [],
                'managements' => [],
                'data' => [],
            ];

            foreach ( $managements as $management )
            {

                $ticketManagements = $management
                    ->tickets()
                    ->mine()
                    ->whereNotIn( 'status_code', [ 'draft' ] )
                    ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                    ->with(
                        'ticket',
                        'ticket.type'
                    )
                    ->get();

                $data[ 'managements' ][ $management->id ] = [
                    'total' => 0,
                    'closed' => 0,
                    'percent' => 0
                ];

                foreach ( $ticketManagements as $ticketManagement )
                {
                    $ticket = $ticketManagement->ticket;
                    $type = $ticket->type;
                    $category_id = $type->parent ? $type->parent->id : $type->id;
                    if ( ! isset( $data[ 'categories' ][ $category_id ] ) )
                    {
                        $data[ 'categories' ][ $category_id ] = [
                            'total' => 0,
                            'closed' => 0,
                            'percent' => 0
                        ];
                    }
                    if ( ! isset( $data[ 'data' ][ $category_id ][ $management->id ] ) )
                    {
                        $data[ 'data' ][ $category_id ][ $management->id ] = [
                            'total' => 0,
                            'closed' => 0,
                            'percent' => 0
                        ];
                    }
                    $data[ 'total' ] ++;
                    $data[ 'managements' ][ $management->id ][ 'total' ] ++;
                    $data[ 'categories' ][ $category_id ][ 'total' ] ++;
                    $data[ 'data' ][ $category_id ][ $management->id ][ 'total' ] ++;
                    switch ( $ticketManagement->status_code )
                    {
                        case 'closed_with_confirm':
                        case 'closed_without_confirm':
                        case 'not_verified':
                        case 'cancel':
                            $data[ 'data' ][ $category_id ][ $management->id ][ 'closed' ] ++;
                            $data[ 'closed' ] ++;
                            $data[ 'managements' ][ $management->id ][ 'closed' ] ++;
                            $data[ 'categories' ][ $category_id ][ 'closed' ] ++;
                            break;
                    }
                }

            }

            $categories_count = 0;

            foreach ( $data[ 'categories' ] as $key => $category )
            {
                if ( $category[ 'total' ] )
                {
                    $categories_count ++;
                    $data[ 'categories' ][ $key ][ 'percent' ] = (float) number_format( $category[ 'closed' ] / $category[ 'total' ] * 100, 1 );
                }
                $data[ 'categories' ][ $key ][ 'percent_total' ] = $data[ 'total' ] ? (float) number_format( $category[ 'total' ] / $data[ 'total' ] * 100, 1 ) : 0;
            }

            $managements_count = 0;

            foreach ( $data[ 'managements' ] as $key => $management )
            {
                if ( $management[ 'total' ] )
                {
                    $managements_count ++;
                    $data[ 'managements' ][ $key ][ 'percent' ] = (float) number_format( $management[ 'closed' ] / $management[ 'total' ] * 100, 1 );
                }
                $data[ 'managements' ][ $key ][ 'percent_total' ] = $data[ 'total' ] ? (float) number_format( $management[ 'total' ] / $data[ 'total' ] * 100, 1 ) : 0;
            }

            $data[ 'percent' ] = $data[ 'total' ] ? (float) number_format( $data[ 'closed' ] / $data[ 'total' ] * 100, 1 ) : 0;

        }

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        $this->addLog( 'Просмотрел отчет по категориям' );

        return view( 'reports.types' )
            ->with( 'data', $data ?? null )
            ->with( 'categories', $categories ?? null )
            ->with( 'managements', $managements ?? null )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'categories_count', $categories_count ?? 0 )
            ->with( 'managements_count', $managements_count ?? 0 )
			->with( 'providers', $providers )
            ->with( 'provider_id', $provider_id )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'managements_ids', $managements_ids );

    }

}
