<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Asterisk\Cdr;
use App\Models\Provider;
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

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->setTime( 0, 0, 0 ) ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );
        $operator_id = $request->get( 'operator_id', null );

        $calls = Cdr
            ::orderBy( 'id', 'desc' )
            ->where( 'dst', '!=', 's' )
            ->whereBetween( 'calldate', [ $date_from->toDateTimeString(), $date_to->toDateTimeString() ] )
            ->select(
                '*',
                \DB::raw( 'REPLACE( src, \'79295070506\', \'88005503115\' ) src' ),
                \DB::raw( 'REPLACE( dst, \'79295070506\', \'88005503115\' ) dst' )
            );

        $providers = Provider
            ::mine()
            ->orderBy( 'name' )
            ->get();

        if ( ! \Auth::user()->admin )
        {
            $providerPhones = [];
            foreach ( $providers as $provider )
            {
                foreach ( $provider->phones as $providerPhone )
                {
                    $providerPhones[] = $providerPhone;
                }
            }
            $calls
                ->whereIn( \DB::raw( 'RIGHT( dst, 10 )' ), $providerPhones );
        }

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
                                        ->whereBetween( 'calldate', [ $phoneSession->created_at->toDateTimeString(), $phoneSession->closed_at ? $phoneSession->closed_at->toDateTimeString() : Carbon::now()->toDateTimeString() ] )
                                        ->where( function ( $q3 ) use ( $phoneSession )
                                        {
                                            return $q3
                                                ->where( 'src', '=', $phoneSession->number )
                                                ->orWhereHas( 'queueLogs', function ( $queueLogs ) use ( $phoneSession )
                                                {
                                                    return $queueLogs
                                                        ->where( \DB::raw( 'REPLACE( agent, \'SIP/\', \'\' )' ), $phoneSession->number );
                                                });
                                        });
                                });
                        }
                    }
                    else
                    {
                        $q->where( 'src', '=', '-1' );
                    }
                    return $q;
                });
        }

        if ( ! empty( $request->get( 'status' ) ) )
        {
            $calls
                ->where( 'disposition', '=', $request->get( 'status' ) );
        }

        switch ( $request->get( 'context' ) )
        {
            case 'incoming':
                $calls
                    ->incoming();
                break;
            case 'outgoing':
                $calls
                    ->outgoing();
                break;
            default:
                $calls
                    ->whereIn( 'dcontext', [ 'incoming', 'outgoing' ] );
                break;
        }

        if ( ! empty( $request->get( 'caller' ) ) )
        {
            $caller = mb_substr( preg_replace( '/\D/', '', $request->get( 'caller' ) ), - 10 );
            $calls
                ->where( \DB::raw( 'RIGHT( REPLACE( src, \'79295070506\', \'88005503115\' ), 10 )' ), '=', $caller );
        }

        if ( ! empty( $request->get( 'answer' ) ) )
        {
            $answer = mb_substr( preg_replace( '/\D/', '', $request->get( 'answer' ) ), - 10 );
            $calls
                ->where( \DB::raw( 'RIGHT( REPLACE( dst, \'79295070506\', \'88005503115\' ), 10 )' ), '=', $answer );
        }

        $calls = $calls
            ->groupBy(
                'uniqueid',
                'disposition'
            )
            ->with(
                'ticket',
                'ticketCall'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

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

        return view('admin.calls.index' )
            ->with( 'calls', $calls )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'availableOperators', $availableOperators )
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

    public function destroy( $id )
    {
        return redirect()->route( 'calls.index' );
    }

}