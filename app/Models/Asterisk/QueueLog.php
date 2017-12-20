<?php

namespace App\Models\Asterisk;

use App\User;
use Carbon\Carbon;

class QueueLog extends BaseModel
{

    protected $table = 'queue_log';

    private $_operator = '-1';

    public static $completeEvents = [ 'COMPLETEAGENT', 'COMPLETECALLER' ];

    public function cdr ()
    {
        return $this->hasMany( 'App\Models\Asterisk\Cdr', 'callid', 'uniqueid' );
    }

    public function scopeCompleted ( $query )
    {
        return $query
            ->whereIn( 'event', self::$completeEvents );
    }

    public function scopeAbandoned ( $query )
    {
        return $query
            ->where( 'event', '=', 'ABANDON' );
    }

    public function number ()
    {
        return preg_replace( '/\D/', '', $this->agent );
    }

    public function operator ()
    {
        if ( $this->_operator == '-1' )
        {
            $datetime = Carbon::parse( $this->time )->toDateTimeString();
            $this->_operator = User
                ::whereHas( 'phoneSession', function ( $q ) use ( $datetime )
                {
                    return $q
                        ->where( 'number', '=', $this->number() )
                        ->where( 'created_at', '<=', $datetime )
                        ->where( 'closed_at', '>=', $datetime );
                })
                ->first();
        }
        return $this->_operator;
    }

    public function isComplete ()
    {
        return in_array( $this->event, self::$completeEvents );
    }

}
