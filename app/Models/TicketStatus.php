<?php

namespace App\Models;

class TicketStatus extends BaseModel
{

    protected $table = 'tickets_statuses';

    protected $fillable = [
        'ticket_id',
        'status_code',
        'status_name'
    ];

    public function ticket ()
    {
        return $this->belongsTo( 'App\Models\Ticket' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new TicketStatus( $attributes );
        $new->save();
        $old = TicketStatus
            ::where( 'ticket_id', '=', $new->ticket_id )
            ->orderBy( 'id', 'desc' )
            ->first();
        if ( $old )
        {
            $diff = ( $new->created_at->timestamp - $old->created_at->timestamp ) / 60 / 60;
            $new->hours = $diff;
            $new->save();
        }
        return $new;
    }

    public static function createFromTicket ( Ticket $ticket )
    {
        $attributes = [
            'ticket_id'         => $ticket->id,
            'status_code'       => $ticket->status_code,
            'status_name'       => $ticket->status_name
        ];
        return self::create( $attributes );
    }

}
