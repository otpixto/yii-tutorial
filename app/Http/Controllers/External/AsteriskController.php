<?php

namespace App\Http\Controllers\External;

use App\Models\PhoneSession;
use App\Models\Provider;
use App\Models\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class AsteriskController extends BaseController
{

    private $asterisk;

    public function __construct ()
    {
        if (Provider::getCurrent()) {
            $this->asterisk = Provider::getCurrent()
                ->getAsterisk();
        }

        parent::__construct();
    }

    public function queue ()
    {
        return $this->asterisk->queue();
    }

    public function queueView ()
    {
        $states = $this->asterisk->queue();
        $channels = array_keys( $states[ 'list' ] );
        if ( count( $channels ) )
        {
            $sessions = PhoneSession
                ::whereIn( 'channel', $channels )
                ->notClosed()
                ->get();
            foreach ( $states[ 'list' ] as $channel => & $state )
            {
                $session = $sessions->where( 'channel', $channel )
                    ->sortByDesc( 'id' )
                    ->first();
                if ( $session && $session->user )
                {
                    $state[ 'operator' ] = $session->user;
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
        return redirect()->route( 'asterisk.queue' );
    }

    public function call ( Request $request )
    {
        $number_from = \Auth::user()->openPhoneSession->number;
        if ( ! $number_from ) return;
        $number_to = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone', '' ) ), - 10 );
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
