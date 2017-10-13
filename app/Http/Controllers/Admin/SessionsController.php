<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\Models\PhoneSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class SessionsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Телефонные сессии' );
    }

    public function index ( Request $request )
    {

        $sessions = PhoneSession
            ::withTrashed()
            ->orderBy( 'id', 'desc' );

        if ( ! empty( $request->get( 'operator' ) ) )
        {
            $sessions
                ->where( 'user_id', '=', $request->get( 'operator' ) );
        }

        if ( ! empty( $request->get( 'number' ) ) )
        {
            $sessions
                ->where( 'number', '=', $request->get( 'number' ) );
        }

        if ( ! empty( $request->get( 'date_from' ) ) )
        {
            $dt = Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString();
            $sessions
                ->whereRaw( 'DATE( created_at ) >= ? AND ( deleted_at IS NULL OR DATE( deleted_at ) >= ? )', [ $dt, $dt ] );
        }

        if ( ! empty( $request->get( 'date_to' ) ) )
        {
            $dt = Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString();
            $sessions
                ->whereRaw( 'DATE( created_at ) <= ? AND DATE( deleted_at ) <= ?', [ $dt, $dt ] );
        }

        $sessions = $sessions
            ->paginate( 30 )
            ->appends( $request->all() );

        $res = User
            ::role( 'operator' )
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        $operators = [];
        foreach ( $res as $r )
        {
            $operators[ $r->id ] = $r->getName();
        }

        return view('admin.sessions.index' )
            ->with( 'sessions', $sessions )
            ->with( 'operators', $operators );

    }

    public function create ()
    {

        Title::add( 'Добавить в очередь' );

        $res = User
            ::role( 'operator' )
            ->orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        $operators = [];
        foreach ( $res as $r )
        {
            $operators[ $r->id ] = $r->getName();
        }

        return view('admin.sessions.create' )
            ->with( 'operators', $operators );

    }

    public function edit ( $id )
    {
        return redirect()->route( 'sessions.index' );
    }

    public function show ( $id )
    {

        $session = PhoneSession
            ::withTrashed()
            ->find( $id );

        if ( ! $session )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( [ 'Сессия не найдена' ] );
        }

        Title::add( 'Телефонная сессия оператора ' . $session->user->getName() );

        $calls = $session->calls();

        return view( 'admin.sessions.show' )
            ->with( 'session', $session )
            ->with( 'calls', $calls );

    }


    public function update ( Request $request, $id )
    {
        return redirect()->route( 'sessions.index' );
    }

    public function store ( Request $request )
    {

        $this->validate( $request, PhoneSession::$rules );

        $asterisk = new Asterisk();
        if ( $asterisk->queueAdd( $request->get( 'number' ) ) )
        {
            $phoneSession = PhoneSession::create( $request->all() );
            if ( $phoneSession instanceof MessageBag )
            {
                $asterisk->queueRemove( $request->get( 'number' ) );
                return redirect()->back()
                    ->withErrors( $phoneSession );
            }
            $phoneSession->save();
        }

        return redirect()->route( 'sessions.index' )
            ->with( 'success', 'Телефон успешно добавлен в очередь' );

    }

    public function destroy( $id )
    {
        $session = PhoneSession::find( $id );
        if ( ! $session )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( [ 'Сессия не найдена' ] );
        }
        $res = $session->user->phoneSessionUnreg();
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( $res );
        }
        return redirect()->route( 'sessions.index' )
            ->with( 'success', 'Сессия успешно закрыта' );
    }

}
