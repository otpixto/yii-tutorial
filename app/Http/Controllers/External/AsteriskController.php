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

        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone', '' ) ), -10 );
        $ticket = Ticket::find( $request->get( 'ticket_id' ) );
		
		$number = \Auth::user()->number ?: \Auth::user()->openPhoneSession->number;

        if ( ! $ticket || ! $ticket->canCall() || ! $number || ( $ticket->phone != $phone && $ticket->phone2 != $phone ) ) return;

        $ticketCall = $ticket->createCall( $phone );

        if ( $ticketCall instanceof MessageBag )
        {
            dd( $ticketCall );
        }

        if ( ! $this->asterisk->originate( 'outgoing', $number, $phone, $ticketCall->id ) )
        {
            dd( $this->asterisk->last_result );
        }

    }

}