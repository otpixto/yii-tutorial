<?php

namespace App\Listeners;

use App\Models\Provider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\MessageBag;

class UserLogin
{
    public function __construct ()
    {
        //
    }
    public function handle ( Login $event )
    {
        if ( ! Provider::isSystemUrl() )
        {
            $log = $event->user->addLog( 'Авторизовался' );
            if ( $log instanceof MessageBag )
            {
                throw new \Exception( $log->first() );
            }
        }
    }
}
