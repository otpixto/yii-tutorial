<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Asterisk\Cdr;
use App\Models\Category;
use App\Models\Executor;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
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

    public function executors ( Request $request )
    {

        Title::add( 'Отчет по исполнителям' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $management_id = $request->get( 'management_id' );
        $executor_id = $request->get( 'executor_id' );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $availableManagements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $ticketManagements = TicketManagement
            ::mine()
            ->whereBetween( 'created_at', [ $date_from, $date_to ] );

        if ( $management_id )
        {
            $ticketManagements
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
        else
        {
            $executors = new Collection();
        }
		
        $ticketManagements = $ticketManagements->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? '' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'reports.executors' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'management', $management ?? null )
            ->with( 'executors', $executors )
            ->with( 'executor', $executor ?? null )
            ->with( 'management_id', $management_id )
            ->with( 'executor_id', $executor_id )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function rates ( Request $request )
    {

        Title::add( 'Отчет по оценкам' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

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
            ->get()
            ->sortBy( 'name' );

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements = $availableManagements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements = $availableManagements;
        }

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

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        return view( 'reports.rates' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function addresses ( Request $request )
    {

        Title::add( 'Отчет по адресам' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $building_id = $request->get( 'building_id' );
        $building = [];

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        if ( $building_id )
        {
            $building = Building::where( 'id', '=', $building_id )->pluck( 'name', 'id' );
            $ticketManagements = TicketManagement
                ::mine()
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->whereHas( 'ticket', function ( $ticket ) use ( $building_id )
                {
                    return $ticket
                        ->where( 'building_id', '=', $building_id );
                })
                ->get();
        }
        else
        {
            $ticketManagements = new Collection();
        }
						
        return view( 'reports.addresses' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'building_id', $building_id )
            ->with( 'building', $building )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function tickets ( Request $request )
    {

        Title::add( 'Отчет по заявкам' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $data = [
            'total' => 0,
            'closed' => 0,
            'not_verified' => 0,
            'canceled' => 0,
            'closed_with_confirm' => 0,
            'closed_without_confirm' => 0
        ];

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements = $availableManagements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements = $availableManagements;
        }

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

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        return view( 'reports.tickets' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function operators ( Request $request )
    {

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
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

        Title::add( 'Отчет по категориям' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        $availableManagements = Management
            ::mine()
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements = $availableManagements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements = $availableManagements;
        }

        $categories = Category
			::whereHas( 'types', function ( $types )
			{
				return $types
					->mine();
			})
            ->orderBy( 'name' )
            ->get();

        $data = [
            'total' => 0,
            'closed' => 0,
            'percent' => 0,
            'categories' => [],
            'managements' => [],
            'data' => [],
        ];

        foreach ( $categories as $category )
        {
            $data[ 'categories' ][ $category->id ] = [
                'total' => 0,
                'closed' => 0,
                'percent' => 0
            ];
        }

        foreach ( $managements as $management )
        {

            $ticketManagements = $management
                ->tickets()
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
                if ( ! isset( $data[ 'data' ][ $type->category_id ][ $management->id ] ) )
                {
                    $data[ 'data' ][ $type->category_id ][ $management->id ] = [
                        'total' => 0,
                        'closed' => 0,
                        'percent' => 0
                    ];
                }
                $data[ 'total' ] ++;
                $data[ 'managements' ][ $management->id ][ 'total' ] ++;
                $data[ 'categories' ][ $type->category_id ][ 'total' ] ++;
                $data[ 'data' ][ $type->category_id ][ $management->id ][ 'total' ] ++;
                switch ( $ticketManagement->status_code )
                {
                    case 'closed_with_confirm':
                    case 'closed_without_confirm':
                    case 'not_verified':
                    case 'cancel':
                        $data[ 'data' ][ $type->category_id ][ $management->id ][ 'closed' ] ++;
                        $data[ 'closed' ] ++;
                        $data[ 'managements' ][ $management->id ][ 'closed' ] ++;
                        $data[ 'categories' ][ $type->category_id ][ 'closed' ] ++;
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

        $res = [];
        foreach ( $availableManagements as $r )
        {
            $res[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
        }

        ksort( $res );

        $availableManagements = $res;

        return view( 'reports.types' )
            ->with( 'data', $data )
            ->with( 'categories', $categories )
            ->with( 'managements', $managements )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'categories_count', $categories_count )
            ->with( 'managements_count', $managements_count )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function map ()
    {
        Title::add( 'География обращений' );
        return view( 'reports.map' );
    }

    public function worksMap ()
    {
        Title::add( 'География работ на сетях' );
        return view( 'reports.works_map' );
    }

}
