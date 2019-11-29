<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Asterisk\Cdr;
use App\Models\Asterisk\MissedCall;
use App\Models\ProviderContext;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CallsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Телефонные звонки' );
    }

    public function index ( Request $request )
    {

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()
            ->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $operator_id = $request->get( 'operator_id', null );

        $providerContexts = ProviderContext
            ::whereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine()
                    ->current();
            } )
            ->groupBy(
                'context'
            )
            ->pluck( 'name', 'context' );

        $calls = Cdr
            ::mine()
            ->orderBy( 'id', 'desc' )
            ->where( 'dst', '!=', 's' )
            ->whereIn( 'dcontext', $providerContexts->keys() )
            ->whereBetween( 'calldate', [ $date_from->toDateTimeString(), $date_to->toDateTimeString() ] );

        if ( $operator_id )
        {
            $calls
                ->where( function ( $q ) use ( $operator_id, $date_from, $date_to )
                {
                    $operator = User::find( $operator_id );
                    $phoneSessions = $operator
                        ->phoneSessions()
                        ->whereBetween( 'created_at', [ $date_from->toDateTimeString(), $date_to->toDateTimeString() ] )
                        ->get();
                    if ( $phoneSessions->count() )
                    {
                        foreach ( $phoneSessions as $phoneSession )
                        {
                            $q
                                ->orWhere( function ( $q2 ) use ( $phoneSession )
                                {
                                    return $q2
                                        ->whereBetween( 'calldate', [ $phoneSession->created_at->toDateTimeString(), $phoneSession->closed_at ? $phoneSession->closed_at->toDateTimeString() : Carbon::now()
                                            ->toDateTimeString() ] )
                                        ->where( function ( $q3 ) use ( $phoneSession )
                                        {
                                            return $q3
                                                ->where( 'src', '=', $phoneSession->number )
                                                ->orWhereHas( 'queueLogs', function ( $queueLogs ) use ( $phoneSession )
                                                {
                                                    return $queueLogs
                                                        ->where( 'agent', '=', $phoneSession->channel );
                                                } );
                                        } );
                                } );
                        }
                    } else
                    {
                        $q->where( 'src', '=', '-1' );
                    }
                    return $q;
                } );
        }

        if ( ! empty( $request->get( 'status' ) ) )
        {
            $calls
                ->where( 'disposition', '=', $request->get( 'status' ) )
                ->groupBy(
                    'uniqueid'
                );
        } else
        {
            $calls
                ->groupBy(
                    'uniqueid',
                    'disposition'
                );
        }

        if ( ! empty( $request->get( 'context' ) ) )
        {
            $calls
                ->where( 'dcontext', '=', $request->get( 'context' ) );
        }

        if ( ! empty( $request->get( 'caller' ) ) )
        {
            $caller = mb_substr( preg_replace( '/\D/', '', $request->get( 'caller' ) ), - 10 );
            $calls
                ->where( \DB::raw( 'RIGHT( src, 10 )' ), '=', $caller );
        }

        if ( ! empty( $request->get( 'answer' ) ) )
        {
            $answer = mb_substr( preg_replace( '/\D/', '', $request->get( 'answer' ) ), - 10 );
            $calls
                ->where( \DB::raw( 'RIGHT( dst, 10 )' ), '=', $answer );
        }

        $calls = $calls
            ->with(
                'ticket',
                'ticketCall'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $res = User
            ::mine()
            ->role( 'operator' )
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        $operators = [];
        foreach ( $res as $r )
        {
            $operators[ $r->id ] = $r->getName();
        }

        $this->addLog( 'Просмотрел звонки (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'admin.calls.index' )
            ->with( 'calls', $calls )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'operators', $operators )
            ->with( 'providerContexts', $providerContexts )
            ->with( 'operator_id', $operator_id );

    }

    public function create ()
    {
        return redirect()->route( 'calls.index' );
    }

    public function edit ( $id )
    {
        return redirect()->route( 'calls.index' );
    }

    public function show ( $id )
    {
        return redirect()->route( 'calls.index' );
    }


    public function update ( Request $request, $id )
    {
        return redirect()->route( 'calls.index' );
    }

    public function store ( Request $request )
    {
        return redirect()->route( 'calls.index' );
    }

    public function destroy ( $id )
    {
        return redirect()->route( 'calls.index' );
    }

    public function missedCalls ()
    {
        Title::add( 'Пропущеныые телефонные звонки' );

        $missedCalls = MissedCall::whereNull( 'call_id' )
            ->with( 'customer' )
            ->get();

        return view( 'admin.calls.missed_calls' )
            ->with( 'missedCalls', $missedCalls );
    }

}
