<?php

namespace App\Models;

class TicketCall extends BaseModel
{

    protected $table = 'tickets_calls';

    public static $name = 'Звонки по заявке';

    public static $rules = [
        'ticket_id'                 => 'required|integer',
        'user_id'                   => 'required|integer',
        'call_id'                   => 'nullable|integer',
        'call_phone'                => 'required|string|max:10',
        'agent_number'              => 'required|string|max:10',
    ];

    protected $nullable = [
        'call_id',
    ];

    protected $fillable = [
        'ticket_id',
        'user_id',
        'call_id',
        'call_phone',
        'agent_number',
    ];

    public function ticket ()
    {
        return $this->belongsTo( 'App\Models\Ticket' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public function cdr ()
    {
        return $this->belongsTo( 'App\Models\Asterisk\Cdr', 'call_id', 'uniqueid' );
    }

}
