<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\Models\PhoneSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class SessionsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Телефонные сессии' );
    }

    public function index ( Request $request )
    {

        $date_from = Carbon::parse( $request->get( 'date_from', Carbon::now()->subMonth() ) );
        $date_to = Carbon::parse( $request->get( 'date_to', Carbon::now() ) );

        $sessions = PhoneSession
            ::orderBy( 'id', 'desc' );

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

        if ( ! empty( $date_from ) )
        {
            $sessions
                ->where( \DB::raw( 'DATE( created_at )' ), '>=', $date_from->toDateString() );
        }

        if ( ! empty( $date_to ) )
        {
            $sessions
                ->where( \DB::raw( 'DATE( created_at )' ), '<=', $date_to->toDateString() );
        }

        $sessions = $sessions
            ->paginate( config( 'pagination.per_page' ) )
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

        $activeSessions = PhoneSession
            ::whereNull( 'closed_at' )
            ->get();

        return view('catalog.sessions.index' )
            ->with( 'sessions', $sessions )
            ->with( 'operators', $operators )
            ->with( 'date_from', $date_from )
            ->with( 'date_to', $date_to )
            ->with( 'activeSessions', $activeSessions );

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

        return view('catalog.sessions.create' )
            ->with( 'operators', $operators );

    }

    public function edit ( $id )
    {
        return redirect()->route( 'sessions.index' );
    }

    public function show ( $id )
    {

        $session = PhoneSession::find( $id );

        if ( ! $session )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( [ 'Сессия не найдена' ] );
        }

        Title::add( 'Телефонная сессия оператора ' . $session->user->getName() );

        $calls = $session->calls();

        return view( 'catalog.sessions.show' )
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
        \DB::beginTransaction();
        if ( ( $res = $asterisk->queueAdd( $request->get( 'number' ) ) ) )
        {
            $phoneSession = PhoneSession::create( $request->all() );
            if ( $phoneSession instanceof MessageBag )
            {
                $asterisk->queueRemove( $request->get( 'number' ) );
                return redirect()->back()
                    ->withErrors( $phoneSession );
            }
            $phoneSession->save();
            $log = $phoneSession->addLog( 'Телефонная сессия началась' );
            if ( $log instanceof MessageBag )
            {
                return redirect()->back()
                    ->withErrors( $log );
            }
        }
        else
        {
            return redirect()->back()
                ->withErrors( $asterisk->last_result );
        }
        \DB::commit();

        return redirect()->route( 'sessions.index' )
            ->with( 'success', 'Телефон успешно добавлен в очередь' );

    }

    public function destroy( $id )
    {
        $phoneSession = PhoneSession::find( $id );
        if ( ! $phoneSession )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( [ 'Сессия не найдена' ] );
        }
        else if ( $phoneSession->closed_at )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( [ 'Сессия уже закрыта' ] );
        }
        //\DB::beginTransaction();
        $log = $phoneSession->addLog( 'Телефонная сессия завершена' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $log );
        }
        $res = $phoneSession->user->phoneSessionUnreg();
        /*if ( $res instanceof MessageBag )
        {
            return redirect()
                ->route( 'sessions.index' )
                ->withErrors( $res );
        }*/
        //\DB::commit();
        return redirect()->route( 'sessions.index' )
            ->with( 'success', 'Сессия успешно закрыта' );
    }

}