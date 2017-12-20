<?php

namespace App\Http\Controllers\External;

use App\Classes\Asterisk;
use App\Models\PhoneSession;

class AsteriskController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function queues ()
    {

        $asterisk = new Asterisk();
        return $asterisk->queues( true );

    }

    public function remove ( $number )
    {

        $asterisk = new Asterisk();
        $asterisk->queueRemove( $number );
        $phoneSession = PhoneSession
            ::where( 'number', '=', $number )
            ->notClosed()
            ->first();
        if ( $phoneSession )
        {
            $phoneSession->close();
        }

    }

}