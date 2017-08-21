<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\Models\PhoneSession;
use App\Models\UserPhoneAuth;
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
            ->with( 'ext_number', $request->get( 'ext_number' ) )
            ->with( 'success', 'На указанный добавочный номер выслан код' );

    }

    public function getPhoneConfirm ( Request $request )
    {
        if ( ! \Session::get( 'ext_number' ) )
        {
            return redirect()->route( 'profile.phone_reg' );
        }
        if ( \Auth::user()->phoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        Title::add( 'Авторизация телефона' );
        return view('profile.phone_confirm' )
            ->with( 'ext_number', \Session::get( 'ext_number' ) );
    }

    public function postPhoneConfirm ( Request $request )
    {

        $res = UserPhoneAuth::confirm( $request->all() );

        if ( $res instanceof MessageBag )
        {
            return redirect()->route( 'profile.phone_reg' )->withErrors( $res );
        }

        $res = PhoneSession::create([
            'user_id'       => \Auth::user()->id,
            'ext_number'    => $request->get( 'ext_number' )
        ]);

        if ( $res instanceof MessageBag )
        {
            return redirect()->route( 'profile.phone_reg' )->withErrors( $res );
        }

        return redirect()->route( 'profile.phone' )
            ->with( 'success', 'Телефон успешно зарегистрирован' );

    }

    public function getPhoneUnreg ()
    {
        $res = \Auth::user()->phoneSessionUnreg();
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $res );
        }
        return redirect()->route( 'profile.phone_reg' )
            ->with( 'success', 'Телефон успешно разлогинен' );
    }

    public function getTest ()
    {
        $asterisk = new Asterisk();
        if ( ! $asterisk->connectTwo( '02', '03' ) )
        {
            dd( $asterisk->last_result );
        }
        //$asterisk->queueRemove( '02' );
        //$asterisk->queueRemove( '03' );
        //$asterisk->queueAdd( '02' );
        //$asterisk->queueAdd( '03' );
        $queues = $asterisk->queues( true );
        dd( $queues );
    }

}
