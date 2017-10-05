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

        Title::add( 'Отчет по ЭО' );

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

        if ( \Auth::user()->hasRole( 'control' ) || \Auth::user()->hasRole( 'operator' ) )
        {

            $tickets = Ticket
                ::mine()
                ->whereNotIn( 'status_code', [ 'draft' ] );

            if ( $date_from )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $date_from )->toDateString() ] );
            }

            if ( $date_to )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $date_to )->toDateString() ] );
            }

            $tickets = $tickets->get();

            foreach ( $tickets as $ticket )
            {

                $managements = $ticket->managements()
                    ->mine()
                    ->whereNotIn( 'status_code', [ 'draft' ] )
                    ->get();

                foreach ( $managements as $management )
                {
                    if ( ! isset( $data[ $management->management_id ] ) )
                    {
                        $data[ $management->management_id ] = [
                            'name' => $management->management->name,
                            'total' => 0,
                            'closed' => 0,
                            'not_verified' => 0,
                            'canceled' => 0,
                            'closed_with_confirm' => 0,
                            'closed_without_confirm' => 0
                        ];
                    }
                    $summary[ 'total' ] ++;
                    $data[ $management->management_id ][ 'total' ] ++;
                    switch ( $management->status_code )
                    {
                        case 'closed_with_confirm':
                            $data[ $management->management_id ][ 'closed' ] ++;
                            $data[ $management->management_id ][ 'closed_with_confirm' ] ++;
                            $summary[ 'closed' ] ++;
                            $summary[ 'closed_with_confirm' ] ++;
                            break;
                        case 'closed_without_confirm':
                            $data[ $management->management_id ][ 'closed' ] ++;
                            $data[ $management->management_id ][ 'closed_without_confirm' ] ++;
                            $summary[ 'closed' ] ++;
                            $summary[ 'closed_without_confirm' ] ++;
                            break;
                        case 'not_verified':
                            $data[ $management->management_id ][ 'closed' ] ++;
                            $data[ $management->management_id ][ 'not_verified' ] ++;
                            $summary[ 'closed' ] ++;
                            $summary[ 'not_verified' ] ++;
                            break;
                        case 'cancel':
                            $data[ $management->management_id ][ 'closed' ] ++;
                            $data[ $management->management_id ][ 'canceled' ] ++;
                            $summary[ 'closed' ] ++;
                            $summary[ 'canceled' ] ++;
                            break;
                    }
                }

            }

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
        {

            $ticketManagements = TicketManagement
                ::mine()
                ->whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
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

            foreach ( $ticketManagements as $management )
            {
                if ( ! isset( $data[ $management->management_id ] ) )
                {
                    $data[ $management->management_id ] = [
                        'name' => $management->management->name,
                        'total' => 0,
                        'closed' => 0,
                        'not_verified' => 0,
                        'canceled' => 0,
                        'closed_with_confirm' => 0,
                        'closed_without_confirm' => 0
                    ];
                }
                $summary[ 'total' ] ++;
                $data[ $management->management_id ][ 'total' ] ++;
                switch ( $management->status_code )
                {
                    case 'closed_with_confirm':
                        $data[ $management->management_id ][ 'closed' ] ++;
                        $data[ $management->management_id ][ 'closed_with_confirm' ] ++;
                        $summary[ 'closed' ] ++;
                        $summary[ 'closed_with_confirm' ] ++;
                        break;
                    case 'closed_without_confirm':
                        $data[ $management->management_id ][ 'closed' ] ++;
                        $data[ $management->management_id ][ 'closed_without_confirm' ] ++;
                        $summary[ 'closed' ] ++;
                        $summary[ 'closed_without_confirm' ] ++;
                        break;
                    case 'not_verified':
                        $data[ $management->management_id ][ 'closed' ] ++;
                        $data[ $management->management_id ][ 'not_verified' ] ++;
                        $summary[ 'closed' ] ++;
                        $summary[ 'not_verified' ] ++;
                        break;
                    case 'cancel':
                        $data[ $management->management_id ][ 'closed' ] ++;
                        $data[ $management->management_id ][ 'canceled' ] ++;
                        $summary[ 'closed' ] ++;
                        $summary[ 'canceled' ] ++;
                        break;
                }
            }

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

        uasort( $data, function ( $a, $b )
        {
            return $a['total'] < $b['total'];
        });

        return view( 'reports.managements' )
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

        if ( \Auth::user()->hasRole( 'control' ) || \Auth::user()->hasRole( 'operator' ) )
        {

            $tickets = Ticket
                ::mine()
                ->whereNotIn( 'status_code', [ 'draft' ] );

            if ( $date_from )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $date_from )->toDateString() ] );
            }

            if ( $date_to )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $date_to )->toDateString() ] );
            }

            $tickets = $tickets->get();

            foreach ( $tickets as $ticket )
            {

                $category = $ticket->type->category;

                $managements = $ticket->managements()
                    ->mine()
                    ->whereNotIn( 'status_code', [ 'draft' ] )
                    ->get();

                foreach ( $managements as $management )
                {
                    if ( ! isset( $data[ $management->management_id ] ) )
                    {
                        $data[ $management->management_id ] = [
                            'name' => $management->management->name,
                            'categories' => []
                        ];
                    }
                    if ( ! isset( $data[ $management->management_id ][ 'categories' ][ $category->id ] ) )
                    {
                        $data[ $management->management_id ][ 'categories' ][ $category->id ] = [
                            'name' => $category->name,
                            'total' => 0,
                            'completed' => 0,
                            'canceled' => 0
                        ];
                    }
                    $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'total' ] ++;
                    switch ( $management->status_code )
                    {
                        case 'completed_with_act':
                        case 'completed_without_act':
                        case 'closed_with_confirm':
                        case 'closed_without_confirm':
                        case 'not_verified':
                            $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'completed' ] ++;
                            break;
                        case 'cancel':
                            $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'canceled' ] ++;
                            break;
                    }
                }

            }

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
        {

            $ticketManagements = TicketManagement
                ::mine()
                ->whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
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

            foreach ( $ticketManagements as $management )
            {
                $ticket = $management->ticket;
                $category = $ticket->type->category;
                if ( ! isset( $data[ $management->management_id ] ) )
                {
                    $data[ $management->management_id ] = [
                        'name' => $management->management->name,
                        'categories' => []
                    ];
                }
                if ( ! isset( $data[ $management->management_id ][ 'categories' ][ $category->id ] ) )
                {
                    $data[ $management->management_id ][ 'categories' ][ $category->id ] = [
                        'name' => $category->name,
                        'total' => 0,
                        'completed' => 0,
                        'canceled' => 0
                    ];
                }
                $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'total' ] ++;
                switch ( $management->status_code )
                {
                    case 'completed_with_act':
                    case 'completed_without_act':
                    case 'closed_with_confirm':
                    case 'closed_without_confirm':
                    case 'not_verified':
                        $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'completed' ] ++;
                        break;
                    case 'cancel':
                        $data[ $management->management_id ][ 'categories' ][ $category->id ][ 'canceled' ] ++;
                        break;
                }
            }

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

        foreach ( $data as & $r )
        {

            uasort( $r['categories'], function ( $a, $b )
            {
                return $a['total'] < $b['total'];
            });

        }

        return view( 'reports.types' )
            ->with( 'data', $data )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

    }

    public function summary ()
    {

    }

}
