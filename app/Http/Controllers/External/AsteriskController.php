<?php

namespace App\Http\Controllers\External;

use App\Classes\Asterisk;
use App\Models\PhoneSession;
use App\Models\Ticket;
use App\User;
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

    public function queues ( $queue = null )
    {
        return $queue ? $this->asterisk->queue( $queue ) : $this->asterisk->queues( true );
    }

    public function queuesView ( $queue )
    {
        $states = $this->asterisk->queue( $queue );
		$numbers = array_keys( $states[ 'list' ] );
		if ( count( $numbers ) )
		{
			$users = User
				::whereIn( 'number', array_keys( $states[ 'list' ] ) )
				->get();
			foreach ( $states[ 'list' ] as $number => & $state )
			{
				$state[ 'operator' ] = $users->where( 'number', $number )->first();
			}
		}
        return view( 'asterisk.list' )
            ->with( 'states', $states );
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

        $number_from = \Auth::user()->number ?: \Auth::user()->openPhoneSession->number;
        if ( ! $number_from ) return;

        $number_to = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone', '' ) ), -10 );
        if ( ! $number_to ) return;

        $ticket = Ticket::find( $request->get( 'ticket_id' ) );
        if ( ! $ticket || ! $ticket->canCall() || ( $ticket->phone != $number_to && $ticket->phone2 != $number_to ) ) return;

        $ticketCall = $ticket->createCall( $number_from, $number_to );

        if ( $ticketCall instanceof MessageBag )
        {
            dd( $ticketCall );
        }

        if ( ! $this->asterisk->originate( $number_from, $number_to, 'outgoing-autodial', $ticketCall->id ) )
        {
            dd( $this->asterisk->last_result );
        }

    }

}