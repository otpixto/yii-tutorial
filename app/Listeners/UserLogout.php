<?php

namespace App\Listeners;

use App\Models\Provider;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\MessageBag;

class UserLogout
{
    public function __construct ()
    {
        //
    }
    public function handle ( Logout $event )
    {
        if ( ! Provider::isSystemUrl() )
        {
            $log = $event->user->addLog( 'Вышел из системы' );
            if ( $log instanceof MessageBag )
            {
                throw new \Exception( $log->first() );
            }
            if ( $event->user->openPhoneSession )
            {
                $res = $event->user->phoneSessionUnreg();
                if ( $res instanceof MessageBag )
                {
                    throw new \Exception( $log->first() );
                }
            }
        }
    }
}
