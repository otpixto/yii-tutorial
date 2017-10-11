<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Ticket;
use App\Models\TicketManagement;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function managements ( Request $request )
    {

        Title::add( 'Отчет по количеству заявок' );

        $data = [];

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

        $summary = [
            'total' => 0,
            'closed' => 0,
            'not_verified' => 0,
            'canceled' => 0,
            'closed_with_confirm' => 0,
            'closed_without_confirm' => 0
        ];

        $ticketManagements = TicketManagement
            ::mine()
            ->whereNotIn( 'status_code', [ 'draft' ] );

        if ( $date_from )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $date_from )->toDateString() ] );
        }

        if ( $date_to )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $date_to )->toDateString() ] );
        }

        $ticketManagements = $ticketManagements->get();

        foreach ( $ticketManagements as $ticketManagement )
        {

            if ( ! isset( $data[ $ticketManagement->management_id ] ) )
            {
                $data[ $ticketManagement->management_id ] = [
                    'name' => $ticketManagement->management->name,
                    'total' => 0,
                    'closed' => 0,
                    'not_verified' => 0,
                    'canceled' => 0,
                    'closed_with_confirm' => 0,
                    'closed_without_confirm' => 0
                ];
            }
            $summary[ 'total' ] ++;
            $data[ $ticketManagement->management_id ][ 'total' ] ++;
            switch ( $ticketManagement->status_code )
            {
                case 'closed_with_confirm':
                    $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                    $data[ $ticketManagement->management_id ][ 'closed_with_confirm' ] ++;
                    $summary[ 'closed' ] ++;
                    $summary[ 'closed_with_confirm' ] ++;
                    break;
                case 'closed_without_confirm':
                    $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                    $data[ $ticketManagement->management_id ][ 'closed_without_confirm' ] ++;
                    $summary[ 'closed' ] ++;
                    $summary[ 'closed_without_confirm' ] ++;
                    break;
                case 'not_verified':
                    $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                    $data[ $ticketManagement->management_id ][ 'not_verified' ] ++;
                    $summary[ 'closed' ] ++;
                    $summary[ 'not_verified' ] ++;
                    break;
                case 'cancel':
                    $data[ $ticketManagement->management_id ][ 'closed' ] ++;
                    $data[ $ticketManagement->management_id ][ 'canceled' ] ++;
                    $summary[ 'closed' ] ++;
                    $summary[ 'canceled' ] ++;
                    break;

            }

        }

        uasort( $data, function ( $a, $b )
        {
            return $a['total'] < $b['total'];
        });

        if ( $request->has( 'export' ) && ( \Auth::user()->admin || \Auth::user()->can( 'reports.export' ) ) )
        {
            $print_data = [];
            foreach ( $data as $r )
            {
                $print_data[] = [
                    'Нименование ЭО'                => $r['name'],
                    'Поступило заявок'              => $r['total'],
                    'Всего закрыто заявок'          => $r['closed'],
                    'Отменено Заявителем'           => $r['canceled'],
                    'Проблема не подтверждена'      => $r['not_verified'],
                    'Закрыто c подтверждением'      => $r['closed_with_confirm'],
                    'Закрыто без подтверждения'     => $r['closed_without_confirm'],
                    '% закрытых заявок'             => ceil( $r['closed'] * 100 / $r['total'] ),
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

        return view( 'reports.managements' )
            ->with( 'data', $data )
            ->with( 'summary', $summary )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function rates ( Request $request )
    {

        Title::add( 'Отчет по оценкам' );

        $data = [];

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

        $summary = [
            'total' => 0,
            'average' => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        $ticketManagements = TicketManagement
            ::mine()
            ->whereNotIn( 'status_code', [ 'draft' ] )
            ->whereNotNull( 'rate' );

        if ( $date_from )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $date_from )->toDateString() ] );
        }

        if ( $date_to )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $date_to )->toDateString() ] );
        }

        $ticketManagements = $ticketManagements->get();

        foreach ( $ticketManagements as $ticketManagement )
        {

            if ( ! isset( $data[ $ticketManagement->management_id ] ) )
            {
                $data[ $ticketManagement->management_id ] = [
                    'name' => $ticketManagement->management->name,
                    'total' => 0,
                    'average' => 0,
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                ];
            }
            $summary[ 'total' ] ++;
            $data[ $ticketManagement->management_id ][ 'total' ] ++;
            $data[ $ticketManagement->management_id ][ $ticketManagement->rate ] ++;
            $summary[ $ticketManagement->rate ] ++;

        }

        $s = 0;
        $c = 0;
        if ( $summary['1'] )
        {
            $s += $summary['1'] * 1;
            $c += $summary['1'];
        }
        if ( $summary['2'] )
        {
            $s += $summary['2'] * 2;
            $c += $summary['2'];
        }
        if ( $summary['3'] )
        {
            $s += $summary['3'] * 3;
            $c += $summary['3'];
        }
        if ( $summary['4'] )
        {
            $s += $summary['4'] * 4;
            $c += $summary['4'];
        }
        if ( $summary['5'] )
        {
            $s += $summary['5'] * 5;
            $c += $summary['5'];
        }
        if ( $c )
        {
            $summary[ 'average' ] = number_format($s / $c, 1, '.', '' );
        }

        uasort( $data, function ( $a, $b )
        {
            return $a['total'] < $b['total'];
        });

        foreach ( $data as & $r )
        {
            $s = 0;
            $c = 0;
            if ( $r['1'] )
            {
                $s += $r['1'] * 1;
                $c += $r['1'];
            }
            if ( $r['2'] )
            {
                $s += $r['2'] * 2;
                $c += $r['2'];
            }
            if ( $r['3'] )
            {
                $s += $r['3'] * 3;
                $c += $r['3'];
            }
            if ( $r['4'] )
            {
                $s += $r['4'] * 4;
                $c += $r['4'];
            }
            if ( $r['5'] )
            {
                $s += $r['5'] * 5;
                $c += $r['5'];
            }
            $r[ 'average' ] = number_format($s / $c, 1, '.', '' );
        }

        if ( $request->has( 'export' ) && \Auth::user()->can( 'reports.export' ) )
        {
            $print_data = [];
            foreach ( $data as $r )
            {
                $print_data[] = [
                    'Нименование ЭО'                => $r['name'],
                    'Всего оценок'                  => $r['total'],
                    '1 балл'                        => $r['1'],
                    '2 балла'                       => $r['2'],
                    '3 балла'                       => $r['3'],
                    '4 балла'                       => $r['4'],
                    '5 баллов'                      => $r['5'],
                    'Средний балл'                  => $r['average'],
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
            ->with( 'summary', $summary )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function addresses ()
    {

    }

    public function types ( Request $request )
    {

        Title::add( 'Отчет по категориям' );

        $data = [];

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

        $summary = [
            'total' => 0,
            'closed' => 0
        ];

        $ticketManagements = TicketManagement
            ::mine()
            ->whereNotIn( 'status_code', [ 'draft' ] );

        if ( $date_from )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $date_from )->toDateString() ] );
        }

        if ( $date_to )
        {
            $ticketManagements
                ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $date_to )->toDateString() ] );
        }

        $ticketManagements = $ticketManagements->get();

        foreach ( $ticketManagements as $ticketManagement )
        {
            $ticket = $ticketManagement->ticket;
            $category = $ticket->type->category;
            if ( ! isset( $data[ $ticketManagement->management_id ] ) )
            {
                $data[ $ticketManagement->management_id ] = [
                    'name' => $ticketManagement->management->name,
                    'categories' => []
                ];
            }
            if ( ! isset( $data[ $ticketManagement->management_id ][ 'categories' ][ $category->id ] ) )
            {
                $data[ $ticketManagement->management_id ][ 'categories' ][ $category->id ] = [
                    'name' => $category->name,
                    'total' => 0,
                    'closed' => 0
                ];
            }
            $summary[ 'total' ] ++;
            $data[ $ticketManagement->management_id ][ 'categories' ][ $category->id ][ 'total' ] ++;
            switch ( $ticketManagement->status_code )
            {
                case 'closed_with_confirm':
                case 'closed_without_confirm':
                case 'not_verified':
                case 'cancel':
                    $data[ $ticketManagement->management_id ][ 'categories' ][ $category->id ][ 'closed' ] ++;
                    $summary[ 'closed' ] ++;
                    break;
            }
        }

        foreach ( $data as & $r )
        {

            uasort( $r['categories'], function ( $a, $b )
            {
                return $a['total'] < $b['total'];
            });

        }

        uasort( $data, function ( $a, $b )
        {
            return strcmp( $a['name'], $b['name'] );
        });

        return view( 'reports.types' )
            ->with( 'data', $data )
            ->with( 'summary', $summary )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function summary ()
    {

    }

}
