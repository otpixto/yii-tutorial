<?php

namespace App\Models\Asterisk;

use App\Models\Provider;
use App\User;
use Carbon\Carbon;

class Cdr extends BaseModel
{

    protected $table = 'cdr';

    protected $dates = [
        'calldate'
    ];

    public static $statuses = [
        'ANSWERED'      => 'Успешно',
        'NO ANSWER'     => 'Нет ответа',
        'BUSY'          => 'Занято',
        'FAILED'        => 'Безуспешно'
    ];

    public $_operator = '-1';

    public function queueLogs ()
    {
        return $this->hasMany( 'App\Models\Asterisk\QueueLog', 'callid', 'uniqueid' );
    }

    public function scopeIncoming ( $query )
    {
        return $query
            ->contexts( 'incoming' );
    }

    public function scopeOutgoing ( $query )
    {
        return $query
            ->contexts( 'outgoing' );
    }

    public function scopeContexts ( $query, ... $contexts )
    {
        return $query
            ->whereIn( \DB::RAW( 'LEFT( dcontext, 8 )' ), $contexts );
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

    public function scopeMine ( $query )
    {
        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->get();
        $providerPhones = [];
        $providerContexts = [];
        foreach ( $providers as $provider )
        {
            $providerContexts[] = $provider->outgoing_context;
            foreach ( $provider->phones as $providerPhone )
            {
                $providerPhones[] = $providerPhone->phone;
            }
            foreach ( $provider->contexts as $providerContext )
            {
                $providerContexts[] = $providerContext->context;
            }
        }
        return $query
            ->where( function ( $q ) use ( $providerPhones, $providerContexts )
            {
                return $q
                    ->whereIn( 'dcontext', $providerContexts )
                    ->orWhereIn( \DB::raw( 'RIGHT( dst, 10 )' ), $providerPhones );
            });
    }

    public function ticket ()
    {
        return $this->belongsTo( 'App\Models\Ticket', 'uniqueid', 'call_id' );
    }

    public function providerPhone ()
    {
        return $this->belongsTo( 'App\Models\ProviderPhone', 'phone', 'phone' );
    }

    public function ticketCall ()
    {
        return $this->belongsTo( 'App\Models\TicketCall', 'uniqueid', 'call_id' );
    }

    public function hasMp3 ()
    {
        $headers = @get_headers( $this->getMp3() );
        return ( $headers[0] == 'HTTP/1.1 200 OK' );
    }

    public function getMp3 ()
    {
        return 'http://' . \Config::get( 'asterisk.external_ip' ) . '/mp3/' . $this->uniqueid . '.mp3';
    }

    public function getContext ()
    {
        return mb_substr( $this->dcontext, 0, 8 );
    }

    public function getOperator ()
    {
        if ( $this->_operator == '-1' )
        {
            switch ( $this->getContext() )
            {
                case 'incoming':
                    $queueLog = $this->queueLogs()->completed()->orderBy( 'time', 'desc' )->first();
                    $this->_operator = $queueLog ? $queueLog->operator() : null;
                    break;
                case 'outgoing':
                    $this->_operator = User
                        ::whereHas( 'phoneSession', function ( $q )
                        {
                            return $q
                                ->where( 'number', '=', $this->src )
                                ->where( 'created_at', '<=', Carbon::parse( $this->calldate )->addSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() )
                                ->where( function ( $q2 )
                                {
                                    return $q2
                                        ->whereNull( 'closed_at' )
                                        ->orWhere( 'closed_at', '>=', Carbon::parse( $this->calldate )->subSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() );
                                });
                        })
                        ->first();
                    break;
                default:
                    $this->_operator = null;
                    break;
            }
        }
        return $this->_operator;
    }

    public function getCaller ()
    {
        $res = mb_substr( $this->src, -11 );
        if ( $this->getContext() == 'outgoing' && mb_strlen( $this->src ) <= 3 )
        {
            $caller = $this->getOperator();
            if ( $caller )
            {
                $res .= ' (' . $caller->getShortName() . ')';
            }
        }
        return $res;
    }

    public function getAnswer ()
    {
        $res = null;
        if ( $this->getContext() == 'incoming' )
        {
            $queueLog = $this->queueLogs()->completed()->orderBy( 'time', 'desc' )->first();
            if ( $queueLog && $queueLog->operator() )
            {
                $res = $queueLog->number() . ' (' . $queueLog->operator()->getShortName() . ')';
            }
        }
        if ( ! $res )
        {
            $res = mb_substr( $this->dst, -11 );
        }
        return $res;
    }

    public function getStatus ()
    {
        return self::$statuses[ $this->disposition ] ?? '-';
    }

    public function isComplete ()
    {
        return $this->queueLogs()->completed()->count() ? true : false;
    }

}
