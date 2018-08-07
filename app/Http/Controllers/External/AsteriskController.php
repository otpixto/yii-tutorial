<?php

namespace App\Http\Controllers\External;

use App\Classes\Asterisk;
use App\Models\PhoneSession;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class AsteriskController extends BaseController
{

    private $asterisk;

    public function __construct ()
    {
        $this->asterisk = new Asterisk();
        parent::__construct();
    }

    public function queues ()
    {
        return $this->asterisk->queues( true );
    }

    public function remove ( $number )
    {

        $this->asterisk->queueRemove( $number );
        $phoneSession = PhoneSession
            ::where( 'number', '=', $number )
            ->notClosed()
            ->first();
        if ( $phoneSession )
        {
            $phoneSession->close();
        }

        return redirect()->route( 'asterisk.queues' );

    }

    public function call ( Request $request )
    {
        if ( ! \Auth::user()->can( 'phone' ) || ! \Auth::user()->openPhoneSession ) return;

        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone', '' ) ), -10 );

        $ticket = Ticket::find( $request->get( 'ticket_id' ) );

        if ( ! $ticket || ( $ticket->phone != $phone && $ticket->phone2 != $phone ) ) return;

        $ticketCall = $ticket->createCall( $phone );

        if ( $ticketCall instanceof MessageBag )
        {
            dd( $ticketCall );
        }

        if ( ! $this->asterisk->connectTwo( \Auth::user()->openPhoneSession->number, $phone, $ticketCall->id ) )
        {
            dd( $this->asterisk->last_result );
        }

    }

}