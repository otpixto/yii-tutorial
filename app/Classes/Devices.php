<?php

namespace App\Classes;

use App\Models\TicketManagement;
use Illuminate\Pagination\LengthAwarePaginator;

class Devices
{

    public static function ticketsInfo ( LengthAwarePaginator $tickets ) : array
    {
        $per_page = $tickets->perPage();
        $page = $tickets->currentPage();
        $total = $tickets->total();
        $pages = ceil( $total / $per_page );
        $response = [
            'tickets' => [],
            'per_page' => $per_page,
            'page' => $page,
            'pages' => $pages,
            'total' => $total
        ];
        foreach ( $tickets as $ticket )
        {
            $response[ 'tickets' ][] = self::ticketInfo( $ticket );
        }
        return $response;
    }

    public static function ticketInfo ( TicketManagement $ticketManagement ) : array
    {
        $ticket = $ticketManagement->ticket;
        $info = [
            'number'        => (int) $ticket->id,
            'id'            => (int) $ticketManagement->id,
            'status_code'   => $ticketManagement->status_code,
            'status_name'   => $ticketManagement->status_name,
            'address'       => $ticket->getAddress( true ),
            'lon'           => ( (float) $ticket->building->lon ) ?: null,
            'lat'           => ( (float) $ticket->building->lat ) ?: null,
            'fullname'      => $ticket->getName(),
            'category'      => $ticket->type->parent->name ?? null,
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
            'history'           => [],
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
        foreach ( $ticket->statusesHistory as $statusHistory )
        {
            $info[ 'history' ][] = [
                'author'        => $statusHistory->author->getName(),
                'datetime'      => $statusHistory->created_at->timestamp,
                'status_code'   => $statusHistory->status_code,
                'status_name'   => $statusHistory->status_name,
            ];
        }
        return $info;
    }

}