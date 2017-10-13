<?php

namespace App\Models\Asterisk;

use App\Models\Ticket;
use Carbon\Carbon;

class Cdr extends BaseModel
{

    protected $table = 'cdr';

    private $_ticket = '-1';

    public function queueLog ()
    {
        return $this->belongsTo( 'App\Models\Asterisk\QueueLog', 'uniqueid', 'callid' );
    }

    public function scopeIncoming ( $query )
    {
        return $query
            ->where( 'dcontext', '=', 'incoming' );
    }

    public function scopeAnswered ( $query )
    {
        return $query
            ->where( 'disposition', '=', 'ANSWERED' );
    }

    public function scopeNoAnswer ( $query )
    {
        return $query
            ->where( 'disposition', '=', 'NO ANSWER' );
    }

    public function scopeMobiles ( $query )
    {
        return $query
            ->where( 'src', '!=', 'anonymous' )
            ->where( 'src', 'not like', '495%' )
            ->where( 'src', 'not like', '499%' )
            ->where( 'src', 'not like', '8495%' )
            ->where( 'src', 'not like', '8499%' );
    }

    public function ticket ()
    {
        if ( $this->_ticket == '-1' )
        {
            $dt_from = Carbon::parse( $this->calldate )->subSeconds( \Config::get( 'asterisk.tolerance' ) );
            $dt_to = Carbon::parse( $this->calldate )->addSeconds( \Config::get( 'asterisk.tolerance' ) );
            $this->_ticket = Ticket
                ::where( 'call_phone', '=', mb_substr( $this->src, -10 ) )
                ->whereBetween( 'call_at', [ $dt_from->toDateTimeString(), $dt_to->toDateTimeString() ] )
                ->where( 'status_code', '!=', 'draft' )
                ->first();
        }
        return $this->_ticket;
    }

    public function hasMp3 ()
    {
        $headers = @get_headers( $this->getMp3() );
        return ( $headers[0] == 'HTTP/1.1 200 OK' );
    }

    public function getMp3 ()
    {
        return 'http://' . \Config::get( 'asterisk.ip' ) . '/mp3/' . $this->uniqueid . '.mp3';
    }

}
