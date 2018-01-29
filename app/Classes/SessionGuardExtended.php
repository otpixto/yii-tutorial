<?php
namespace App\Classes;

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class SessionGuardExtended extends SessionGuard
{

    public function login ( AuthenticatableContract $user, $remember = false )
    {
        if ( $user )
        {
            $log = $user->addLog( 'Авторизовался с IP ' . \Input::ip() . ' Host ' . \Input::getHost(), $user->id );
            if ( $log instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $log );
            }
        }
        $this->updateSession( $user->getAuthIdentifier() );
        $this->fireLoginEvent( $user, $remember );
        $this->setUser( $user );
        #parent::login( $user, $remember );
    }

    public function logout ()
    {
        $user = $this->user();
        if ( $user )
        {
            $log = $user->addLog( 'Выход из системы с IP ' . \Input::ip() . ' Host ' . \Input::getHost() );
            if ( $log instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $log );
            }
            if ( $user->openPhoneSession )
            {
                $res = $user->phoneSessionUnreg();
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withErrors( $res );
                }
            }
        }
        parent::logout();
    }

}