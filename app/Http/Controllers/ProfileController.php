<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\Models\PhoneSession;
use App\Models\Ticket;
use App\Models\UserPhoneAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ProfileController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Профиль пользователя' );
    }


    public function getPhone ()
    {

        if ( ! \Auth::user()->phoneSession )
        {
            return redirect()->route( 'profile.phone_reg' );
        }

        Title::add( 'Информация об авторизациях' );

        return view('profile.phone' );

    }

    public function getPhoneReg ()
    {
        if ( \Auth::user()->phoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        Title::add( 'Регистрация телефона' );
        return view('profile.phone_reg' );
    }

    public function postPhoneReg ( Request $request )
    {

        $res = UserPhoneAuth::create( $request->all() );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $res );
        }

        return redirect()->route( 'profile.phone_confirm' )
            ->with( 'number', $request->get( 'number' ) )
            ->with( 'success', 'На указанный добавочный номер выслан код' );

    }

    public function getPhoneConfirm ( Request $request )
    {
        if ( ! \Session::get( 'number' ) )
        {
            return redirect()->route( 'profile.phone_reg' );
        }
        if ( \Auth::user()->phoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        Title::add( 'Авторизация телефона' );
        return view('profile.phone_confirm' )
            ->with( 'number', \Session::get( 'number' ) );
    }

    public function postPhoneConfirm ( Request $request )
    {
        $res = UserPhoneAuth::confirm( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->route( 'profile.phone_reg' )->withErrors( $res );
        }
        \DB::beginTransaction();
        $phoneSession = PhoneSession::create([
            'user_id'       => \Auth::user()->id,
            'number'        => $request->get( 'number' )
        ]);
        if ( $phoneSession instanceof MessageBag )
        {
            return redirect()
                ->route( 'profile.phone_reg' )
                ->withErrors( $phoneSession );
        }
        $phoneSession->save();
        $log = $phoneSession->addLog( 'Телефонная сессия началась' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $log );
        }
        \DB::commit();
        return redirect()->route( 'profile.phone' )
            ->with( 'success', 'Телефон успешно зарегистрирован' );
    }

    public function postPhoneUnreg ()
    {
        \DB::beginTransaction();
        $log = \Auth::user()->phoneSession->addLog( 'Телефонная сессия завершена' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $log );
        }
        $res = \Auth::user()->phoneSessionUnreg();
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $res );
        }
        \DB::commit();
        return redirect()->route( 'profile.phone_reg' )
            ->with( 'success', 'Телефон успешно разлогинен' );
    }

    public function getTest ()
    {
        set_time_limit(0);
        $ticket = Ticket
            ::whereNotNull('call_id' )
            ->first();
        dd( $ticket, $ticket->cdr );
        $asterisk = new Asterisk();
        dd( $asterisk->queues() );
    }

    public function getFix ( $number )
    {

        $asterisk = new Asterisk();
        $asterisk->queueRemove( $number );
        $phoneSession = PhoneSession::where( 'number', '=', $number )->first();
        if ( $phoneSession )
        {
            $phoneSession->delete();
        }

    }

}