<?php

namespace App\Models\Asterisk;

use App\Models\Ticket;
use App\User;
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

    public function scopeOutgoing ( $query )
    {
        return $query
            ->where( 'dcontext', '!=', 'incoming' );
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

    public function getCaller ()
    {
        $res = null;
        if ( $this->dcontext != 'incoming' && mb_strlen( $this->src ) == 2 )
        {
            $datetime = Carbon::parse( $this->calldate )->toDateTimeString();
            $caller = User
                ::whereHas( 'phoneSession', function ( $q ) use ( $datetime )
                {
                    return $q
                        ->where( 'number', '=', $this->src )
                        ->where( 'created_at', '<=', $datetime )
                        ->where( 'closed_at', '>=', $datetime );
                })
                ->first();
            if ( $caller )
            {
                $res = $this->src . ' (' .$caller->getShortName() . ')';
            }
        }
        if ( ! $res )
        {
            $res = mb_substr( $this->src, -10 );
        }
        return $res;
    }

    public function getAnswer ( $html = false )
    {
        $res = null;
        if ( $this->dcontext == 'incoming' )
        {
            $queueLog = $this->queueLog()->completed()->first();
            if ( $queueLog && $queueLog->operator() )
            {
                $res = $queueLog->number() . ' (' . $queueLog->operator()->getShortName() . ')';
            }
        }
        if ( ! $res )
        {
            $res = mb_substr( $this->dst, -10 );
        }
        return $res;
    }

    public function getStatus ()
    {
        return self::$statuses[ $this->disposition ] ?? '-';
    }

}
