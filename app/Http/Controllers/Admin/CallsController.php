<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Asterisk\Cdr;
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

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        $calls = Cdr
            ::orderBy( 'id', 'desc' )
            ->where( 'dst', '!=', 's' )
            ->select(
                '*',
                \DB::raw( 'REPLACE( src, \'79295070506\', \'88005503115\' ) src' ),
                \DB::raw( 'REPLACE( dst, \'79295070506\', \'88005503115\' ) dst' )
            );

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

        if ( ! empty( $date_from ) )
        {
            $calls
                ->where( \DB::raw( 'DATE( calldate )' ), '>=', $date_from->toDateString() );
        }

        if ( ! empty( $date_to ) )
        {
            $calls
                ->where( \DB::raw( 'DATE( calldate )' ), '<=', $date_to->toDateString() );
        }

        $calls = $calls
            ->groupBy(
                'uniqueid',
                'disposition'
            )
            ->paginate( 30 )
            ->appends( $request->all() );

        return view('admin.calls.index' )
            ->with( 'calls', $calls )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to );

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