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

        if ( \Auth::user()->hasRole( 'control' ) || \Auth::user()->hasRole( 'operator' ) )
        {

            $tickets = Ticket
                ::mine()
                ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] );

            if ( $request->get( 'date_from' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )->toDateString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )->toDateString() ] );
            }

            $tickets = $tickets->get();

            foreach ( $tickets as $ticket )
            {

                $managements = $ticket->managements()
                    ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] )
                    ->get();

                foreach ( $managements as $management )
                {
                    if ( ! isset( $data[ $management->management_id ] ) )
                    {
                        $data[ $management->management_id ] = [
                            'name' => $management->management->name,
                            'total' => 0,
                            'completed' => 0
                        ];
                    }
                    $data[ $management->management_id ][ 'total' ] ++;
                    switch ( $management->status_code )
                    {
                        case 'completed_with_act':
                        case 'completed_without_act':
                        case 'closed_with_confirm':
                        case 'closed_without_confirm':
                        case 'not_verified':
                            $data[ $management->management_id ][ 'completed' ] ++;
                            break;
                    }
                }

            }

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
        {

            $ticketManagements = TicketManagement
                ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
                ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] )
                ->mine();

            if ( $request->get( 'date_from' ) )
            {
                $ticketManagements
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )->toDateString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $ticketManagements
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )->toDateString() ] );
            }

            $ticketManagements = $ticketManagements->get();

            foreach ( $ticketManagements as $management )
            {
                if ( ! isset( $data[ $management->management_id ] ) )
                {
                    $data[ $management->management_id ] = [
                        'name' => $management->management->name,
                        'total' => 0,
                        'completed' => 0
                    ];
                }
                $data[ $management->management_id ][ 'total' ] ++;
                switch ( $management->status_code )
                {
                    case 'completed_with_act':
                    case 'completed_without_act':
                    case 'closed_with_confirm':
                    case 'closed_without_confirm':
                    case 'not_verified':
                        $data[ $management->management_id ][ 'completed' ] ++;
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

        return view( 'reports.managements' )
            ->with( 'data', $data );

    }

    public function addresses ()
    {

    }

    public function types ( Request $request )
    {

        Title::add( 'Отчет по категориям' );

        $data = [];

        if ( \Auth::user()->hasRole( 'control' ) || \Auth::user()->hasRole( 'operator' ) )
        {

            $tickets = Ticket
                ::mine()
                ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] );

            if ( $request->get( 'date_from' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )->toDateString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )->toDateString() ] );
            }

            $tickets = $tickets->get();

            foreach ( $tickets as $ticket )
            {

                $category = $ticket->type->category;

                $managements = $ticket->managements()
                    ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] )
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
                            'completed' => 0
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
                    }
                }

            }

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
        {

            $ticketManagements = TicketManagement
                ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
                ->whereNotIn( 'status_code', [ 'draft', 'cancel' ] )
                ->mine();

            if ( $request->get( 'date_from' ) )
            {
                $ticketManagements
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )->toDateString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $ticketManagements
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )->toDateString() ] );
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
                        'completed' => 0
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
                }
            }

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

        return view( 'reports.types' )
            ->with( 'data', $data );

    }

    public function summary ()
    {

    }

}
