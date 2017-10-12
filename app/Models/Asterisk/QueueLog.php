<?php

namespace App\Models\Asterisk;

use App\User;
use Carbon\Carbon;

class QueueLog extends BaseModel
{

    protected $table = 'queue_log';

    private $_operator = '-1';

    public function cdr ()
    {
        return $this->hasMany( 'App\Models\Asterisk\Cdr', 'callid', 'uniqueid' );
    }

    public function scopeCompleted ( $query )
    {
        return $query
            ->where( 'event', '=', 'COMPLETEAGENT' );
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
                        ->withTrashed()
                        ->where( 'number', '=', $this->number() )
                        ->where( 'created_at', '<=', $datetime )
                        ->where( 'deleted_at', '>=', $datetime );
                })
                ->first();
        }
        return $this->_operator;
    }

}
