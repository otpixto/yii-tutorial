<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Category;
use App\Models\Management;
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

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

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
            ->get();

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

        return view( 'reports.managements' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function rates ( Request $request )
    {

        Title::add( 'Отчет по оценкам' );

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

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

        return view( 'reports.rates' )
            ->with( 'data', $data )
            ->with( 'managements', $managements )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function addresses ()
    {

        Title::add( 'Отчет по адресам' );

    }

    public function types ( Request $request )
    {

        Title::add( 'Отчет по категориям' );

        $date_from = $request->get( 'date_from', Carbon::now()->subMonth()->startOfMonth()->format( 'd.m.Y' ) );
        $date_to = $request->get( 'date_to', Carbon::now()->subMonth()->endOfMonth()->format( 'd.m.Y' ) );

        $managements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $categories = Category
            ::orderBy( 'name' )
            ->get();

        if ( \Cache::has( 'reports-types-' . Carbon::parse( $date_from )->toDateString() . '-' . Carbon::parse( $date_to )->toDateString() ) )
        {
            $data = \Cache::get( 'reports-types-' . Carbon::parse( $date_from )->toDateString() . '-' . Carbon::parse( $date_to )->toDateString() );
        }
        else
        {

            $data = [
                'total' => 0,
                'closed' => 0
            ];

            foreach ( $managements as $management )
            {

                $ticketManagements = $management
                    ->tickets()
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

            \Cache::put( $data, 60 );

        }

        return view( 'reports.types' )
            ->with( 'data', $data )
            ->with( 'categories', $categories )
            ->with( 'managements', $managements )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function summary ()
    {

    }

}
