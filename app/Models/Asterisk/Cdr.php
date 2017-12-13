<?php

namespace App\Models\Asterisk;

use App\Models\Ticket;
use Carbon\Carbon;

class Cdr extends BaseModel
{

    protected $table = 'cdr';

    public static $statuses = [
        'ANSWERED'      => 'Успешно',
        'NO ANSWER'     => 'Нет ответа',
        'BUSY'          => 'Занято',
        'FAILED'        => 'Безуспешно'
    ];

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
        return $this->belongsTo( 'App\Models\Ticket', 'uniqueid', 'call_id' );
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

    public function getCaller ( $html = false )
    {
        if ( mb_strlen( $this->src ) >= 10 )
        {
            $phone = mb_substr( $this->src, -10 );
            if ( $html )
            {
                $phone = '<a href="tel:7' . $phone . '" class="inherit">' . $phone . '</a';
            }
        }
        else
        {
            $phone = $this->src;
        }
        return $phone;
    }

    public function getAnswer ( $html = false )
    {
        if ( mb_strlen( $this->dst ) >= 10 )
        {
            $phone = mb_substr( $this->dst, -10 );
            if ( $html )
            {
                $phone = '<a href="tel:7' . $phone . '" class="inherit">' . $phone . '</a';
            }
        }
        else
        {
            $phone = $this->dst;
        }
        return $phone;
    }

    public function getStatus ()
    {
        return self::$statuses[ $this->disposition ] ?? '-';
    }

}
