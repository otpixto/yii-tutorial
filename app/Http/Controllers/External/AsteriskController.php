<?php

namespace App\Http\Controllers\External;

use App\Models\PhoneSession;
use App\Models\Provider;
use App\Models\Ticket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class AsteriskController extends BaseController
{

    private $asterisk;

    public function __construct ()
    {
        $this->asterisk = Provider::getCurrent()->getAsterisk();
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
				::whereIn( 'number', $numbers )
				->get();
			foreach ( $states[ 'list' ] as $channel => & $state )
			{
			    $operator = $users->where( 'channel', $channel )->first();
			    if ( $operator )
                {
                    $state[ 'operator' ] = $operator;
                }
                else
                {
                    $states[ 'count' ] --;
                    if ( config( 'asterisk.remove_unreg' ) )
                    {
                        $this->asterisk->queueRemoveByChannel( $channel );
                        unset( $states[ 'list' ][ $channel ] );
                    }
                }
			}
		}
        return view( 'asterisk.list' )
            ->with( 'states', $states );
    }

    public function add ( Request $request, $exten )
    {
        $this->asterisk->queueAddByExten( $exten, $request->get( 'queue' ) );
    }

    public function remove ( Request $request, $exten )
    {
        $this->asterisk->queueRemoveByExten( $exten, $request->get( 'queue' ) );
        $phoneSession = PhoneSession
            ::where( 'number', '=', $exten )
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
        $number_from = \Auth::user()->openPhoneSession->number;
        if ( ! $number_from ) return;
        $number_to = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone', '' ) ), -10 );
        if ( ! $number_to ) return;
        $ticket = Ticket::find( $request->get( 'ticket_id' ) );
        if ( ! $ticket || ! $ticket->canCall() ) return;
        $ticketCall = $ticket->createCall( $number_from, $number_to );
        if ( $ticketCall instanceof MessageBag )
        {
            dd( $ticketCall );
        }
        $rest_curl_url = config( 'rest.curl_url' ) . '/ticket-call?ticket_call_id=' . (int) $ticketCall->id;
        if ( ! $this->asterisk->originate( $number_from, $number_to, $number_from, $rest_curl_url ) )
        {
            dd( $this->asterisk->last_result );
        }
    }

}