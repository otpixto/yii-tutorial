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
            'id'            => $ticket->id,
            'id2'           => $ticketManagement->id,
            'status_code'   => $ticketManagement->status_code,
            'status_name'   => $ticketManagement->status_name,
            'address'       => $ticket->getAddress( true ),
            'fullname'      => $ticket->getName(),
            'category'      => $ticket->type->category->name ?? null,
            'type'          => $ticket->type->name,
            'text'          => $ticket->text,
            'phone'         => $ticket->phone,
            'phone2'        => $ticket->phone2,
            'operator'      => [
                'fullname'  => $ticket->author->getName(),
                'exten'     => $ticket->author->exten
            ],
            'comments'      => [],
            'scheduled_begin'   => $ticketManagement->scheduled_begin->timestamp ?? null,
            'scheduled_end'     => $ticketManagement->scheduled_end->timestamp ?? null,
        ];
        foreach ( $ticket->comments as $comment )
        {
            $info[ 'comments' ][] = [
                'author'    => $comment->author->getName(),
                'datetime'  => $comment->created_at->format( 'd.m.Y H:i' ),
                'text'      => $comment->text
            ];
        }
        return $info;
    }

}