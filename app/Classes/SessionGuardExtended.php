<?php
namespace App\Classes;

use App\Models\Provider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class SessionGuardExtended extends SessionGuard
{

    public function login ( AuthenticatableContract $user, $remember = false )
    {
        if ( $user && ! Provider::isSystemUrl() )
        {
            $log = $user->addLog( 'Авторизовался' );
            if ( $log instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $log );
            }
        }
        parent::login( $user );
    }

    public function logout ()
    {
        $user = $this->user();
        if ( $user )
        {
            $log = $user->addLog( 'Вышел из системы' );
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