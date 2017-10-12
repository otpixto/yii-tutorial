<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\Models\PhoneSession;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class SessionsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Авторизация на телефоне' );
    }

    public function index ()
    {

        $sessions = PhoneSession
            ::withTrashed()
            ->orderBy( 'id', 'desc' )
            ->paginate( 30 );

        return view('admin.sessions.index' )
            ->with( 'sessions', $sessions );

    }

    public function create ()
    {

        Title::add( 'Добавить в очередь' );

        $res = User
            ::orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        $users = [];
        foreach ( $res as $r )
        {
            $users[ $r->id ] = $r->getName();
        }

        return view('admin.sessions.create' )
            ->with( 'users', $users );

    }

    public function edit ( $id )
    {
        return redirect()->route( 'sessions.index' );
    }

    public function show ( $id )
    {
        return redirect()->route( 'sessions.index' );
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

}
