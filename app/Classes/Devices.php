<?php

namespace App\Classes;

use App\Models\TicketManagement;
use Illuminate\Pagination\LengthAwarePaginator;

class Devices
{

    public static function ticketsInfo ( LengthAwarePaginator $tickets, $withDetails = false ) : array
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
            $response[ 'tickets' ][] = self::ticketInfo( $ticket, $withDetails );
        }
        return $response;
    }

    public static function ticketInfo ( TicketManagement $ticketManagement, $withDetails = false ) : array
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
                'prefix'        => $ticket->author->prefix,
                'fullname'      => $ticket->author->getName(),
                'number'        => $ticket->author->number
            ],
            'scheduled_begin'   => $ticketManagement->scheduled_begin->timestamp ?? null,
            'scheduled_end'     => $ticketManagement->scheduled_end->timestamp ?? null,
        ];
        if ( $withDetails )
        {
            $info[ 'comments' ] = [];
            $info[ 'calls' ] = [];
            $info[ 'history' ] = [];
            foreach ( $ticket->comments as $comment )
            {
                $info[ 'comments' ][] = [
                    'operator'      => [
                        'prefix'        => $comment->author->prefix,
                        'fullname'      => $comment->author->getName(),
                        'number'        => $comment->author->number
                    ],
                    'datetime'  => $comment->created_at->timestamp,
                    'text'      => $comment->text
                ];
            }
            foreach ( $ticket->calls as $call )
            {
                $info[ 'calls' ][] = [
                    'operator'      => [
                        'prefix'        => $call->author->prefix,
                        'fullname'      => $call->author->getName(),
                        'number'        => $call->author->number
                    ],
                    'datetime'      => $call->created_at->timestamp,
                    'number_from'   => $call->agent_number,
                    'number_to'     => $call->call_phone,
                ];
            }
            foreach ( $ticket->statusesHistory as $statusHistory )
            {
                $info[ 'history' ][] = [
                    'operator'      => [
                        'prefix'        => $statusHistory->author->prefix,
                        'fullname'      => $statusHistory->author->getName(),
                        'number'        => $statusHistory->author->number
                    ],
                    'datetime'      => $statusHistory->created_at->timestamp,
                    'status_code'   => $statusHistory->status_code,
                    'status_name'   => $statusHistory->status_name,
                ];
            }
        }
        return $info;
    }

}