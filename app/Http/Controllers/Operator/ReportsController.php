<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Asterisk\Cdr;
use App\Models\Category;
use App\Models\Executor;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
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

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $management_id = $request->get( 'management_id' );
        $executor_id = $request->get( 'executor_id' );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $managements = Management
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
            $management = $managements->find( $management_id );
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

        return view( 'reports.executors' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'managements', $managements )
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

        $managements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements2 = $managements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements2 = $managements;
        }

        foreach ( $managements2 as $management )
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

        foreach ( $managements2 as $management )
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
            foreach ( $managements2 as $management )
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

        return view( 'reports.rates' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'managements2', $managements2 )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function addresses ( Request $request )
    {

        Title::add( 'Отчет по адресам' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $address_id = $request->get( 'address_id' );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        if ( $address_id )
        {
            $address = Address::find( $address_id );
            $ticketManagements = TicketManagement
                ::mine()
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->whereHas( 'ticket', function ( $ticket ) use ( $address_id )
                {
                    return $ticket
                        ->where( 'address_id', '=', $address_id );
                })
                ->get();
        }
        else
        {
            $ticketManagements = new Collection();
        }

        return view( 'reports.addresses' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'address_id', $address_id )
            ->with( 'address', $address ?? null )
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

        $managements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements2 = $managements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements2 = $managements;
        }

        foreach ( $managements2 as $management )
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
            foreach ( $managements2 as $management )
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

        return view( 'reports.tickets' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'managements2', $managements2 )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function operators ( Request $request )
    {

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        if ( $date_from->timestamp > $date_to->timestamp )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $res = Cdr
            ::incoming()
            ->mine()
            ->whereBetween( 'calldate', [ $date_from, $date_to ] )
            ->whereHas( 'queueLog' )
            ->groupBy( 'uniqueid' )
            ->get();

        $data = [];

        if ( $date_from == $date_to )
        {
            $format = 'Hч.';
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
                    'calls' => 0,
                    'duration' => 0,
                    'tickets' => 0,
                ];
            }
            $data[ $date ][ 'calls' ] ++;
            if ( $r->queueLog->isComplete() )
            {
                $data[ $date ][ 'duration' ] += $r->duration;
            }
        }

        if ( $date_from == $date_to )
        {
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
                        'calls' => 0,
                        'duration' => 0,
                        'tickets' => 0,
                    ];
                }
                $current_date = $current_date->addDay();
            }
        }

        $res = Ticket
            ::mine()
            ->whereBetween( 'created_at', [ $date_from, $date_to ] )
            ->get();

        foreach ( $res as $r )
        {
            $date = $r->created_at->format( $format );
            if ( ! isset( $data[ $date ] ) )
            {
                $data[ $date ] = [
                    'calls' => 0,
                    'duration' => 0,
                    'tickets' => 0,
                ];
            }
            $data[ $date ][ 'tickets' ] ++;
        }

        ksort( $data );

        return view( 'reports.operators' )
            ->with( 'data', $data )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function types ( Request $request )
    {

        Title::add( 'Отчет по категориям' );

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->startOfMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        $managements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $categories = Category
            ::orderBy( 'name' )
            ->get();

        $data = [
            'total' => 0,
            'closed' => 0
        ];

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $managements2 = $managements
                ->whereIn( 'id', $request->get( 'managements', [] ) );
        }
        else
        {
            $managements2 = $managements;
        }

        foreach ( $managements2 as $management )
        {

            $ticketManagements = $management
                ->tickets()
                ->whereNotIn( 'status_code', [ 'draft' ] )
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->get();

            foreach ( $ticketManagements as $ticketManagement )
            {
                $ticket = $ticketManagement->ticket;
                $type = $ticket->type;
                if ( ! isset( $data[ 'management-' . $management->id ] ) )
                {
                    $data[ 'management-' . $management->id ] = [
                        'total' => 0,
                        'closed' => 0
                    ];
                }
                if ( ! isset( $data[ 'category-' . $type->category_id ] ) )
                {
                    $data[ 'category-' . $type->category_id ] = [
                        'total' => 0,
                        'closed' => 0
                    ];
                }
                if ( ! isset( $data[ $type->category_id ][ $management->id ] ) )
                {
                    $data[ $type->category_id ][ $management->id ] = [
                        'total' => 0,
                        'closed' => 0
                    ];
                }
                $data[ 'total' ] ++;
                $data[ 'management-' . $management->id ][ 'total' ] ++;
                $data[ 'category-' . $type->category_id ][ 'total' ] ++;
                $data[ $type->category_id ][ $management->id ][ 'total' ] ++;
                switch ( $ticketManagement->status_code )
                {
                    case 'closed_with_confirm':
                    case 'closed_without_confirm':
                    case 'not_verified':
                    case 'cancel':
                        $data[ $type->category_id ][ $management->id ][ 'closed' ] ++;
                        $data[ 'closed' ] ++;
                        $data[ 'management-' . $management->id ][ 'closed' ] ++;
                        $data[ 'category-' . $type->category_id ][ 'closed' ] ++;
                        break;
                }
            }

        }

        return view( 'reports.types' )
            ->with( 'data', $data )
            ->with( 'categories', $categories )
            ->with( 'managements', $managements )
            ->with( 'managements2', $managements2 )
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
