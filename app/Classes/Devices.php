<?php

namespace App\Classes;

use App\Models\Ticket;
use App\Models\TicketManagement;
use Illuminate\Support\Collection;

class Devices
{

    public static function ticketsInfo ( Collection $tickets )
    {
        $response = [];
        foreach ( $tickets as $ticket )
        {
            $response[] = self::ticketInfo( $ticket );
        }
        return $response;
    }

    public static function ticketInfo ( TicketManagement $ticketManagement )
    {
        $ticket = $ticketManagement->ticket;
        $info = [
            'number'        => (int) $ticket->id,
            'id'            => (int) $ticketManagement->id,
            'status_code'   => $ticketManagement->status_code,
            'status_name'   => $ticketManagement->status_name,
            'address'       => $ticket->getAddress( true ),
            'lon'           => (float) $ticket->building->lon,
            'lat'           => (float) $ticket->building->lat,
            'fullname'      => $ticket->getName(),
            'category'      => $ticket->type->category->name ?? null,
            'type'          => $ticket->type->name,
            'need_act'      => $ticketManagement->needAct(),
            'text'          => $ticket->text,
            'phone'         => $ticket->phone,
            'phone2'        => $ticket->phone2,
            'operator'      => [
                'fullname'      => $ticket->author->getName(),
                'number'        => $ticket->author->number
            ],
            'scheduled_begin'   => $ticketManagement->scheduled_begin->timestamp ?? null,
            'scheduled_end'     => $ticketManagement->scheduled_end->timestamp ?? null,
            'comments'          => [],
            'calls'             => [],
        ];
        foreach ( $ticket->comments as $comment )
        {
            $info[ 'comments' ][] = [
                'author'    => $comment->author->getName(),
                'datetime'  => $comment->created_at->timestamp,
                'text'      => $comment->text
            ];
        }
        foreach ( $ticket->calls as $call )
        {
            $info[ 'calls' ][] = [
                'author'        => $call->author->getName(),
                'datetime'      => $call->created_at->timestamp,
                'number_from'   => $call->agent_number,
                'number_to'     => $call->call_phone,
            ];
        }
        return $info;
    }

}