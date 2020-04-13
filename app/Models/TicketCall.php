<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;

class TicketCall extends BaseModel
{

    protected $table = 'tickets_calls';
    public static $_table = 'tickets_calls';

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
        return $this->belongsTo( Ticket::class );
    }

    public function cdr ()
    {
        return $this->belongsTo( Cdr::class, 'call_id', 'uniqueid' );
    }

    public function scopeActual ( $query )
    {
        return $query
            ->whereNotNull( self::$_table . '.call_id' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->where( self::$_table . '.author_id', '=', \Auth::user()->id );
    }

}
