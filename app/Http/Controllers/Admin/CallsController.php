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

        $calls = Cdr
            ::orderBy( 'id', 'desc' )
            ->whereIn( 'dcontext', [ 'incoming', 'outgoing' ] );

        if ( ! empty( $request->get( 'status' ) ) )
        {
            $calls
                ->where( 'disposition', '=', $request->get( 'status' ) );
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

        if ( ! empty( $request->get( 'date_from' ) ) )
        {
            $date_from = Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString();
            $calls
                ->where( \DB::raw( 'DATE( calldate )' ), '>=', $date_from );
        }

        if ( ! empty( $request->get( 'date_to' ) ) )
        {
            $date_to = Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString();
            $calls
                ->where( \DB::raw( 'DATE( calldate )' ), '<=', $date_to );
        }

        $calls = $calls
            ->groupBy(
                'uniqueid',
                'disposition'
            )
            ->paginate( 30 )
            ->appends( $request->all() );

        return view('admin.calls.index' )
            ->with( 'calls', $calls );

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