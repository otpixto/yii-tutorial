<?php

namespace App\Http\Controllers;

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
        if ( ! \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone_reg' );
        }
        Title::add( 'Информация об авторизациях' );
        return view('profile.phone' );
    }

    public function getPhoneReg ()
    {
        if ( \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        Title::add( 'Регистрация телефона' );
        return view('profile.phone_reg' );
    }

    public function postPhoneReg ( Request $request )
    {
        if ( \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        $phoneAuth = UserPhoneAuth::create( $request->all() );
        if ( $phoneAuth instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $phoneAuth );
        }
        return view( 'profile.phone_confirm' )
            ->with( 'phoneAuth', $phoneAuth );
    }

    public function postPhoneUnreg ()
    {
        \DB::beginTransaction();
        $log = \Auth::user()->openPhoneSession->addLog( 'Телефонная сессия завершена' );
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

}