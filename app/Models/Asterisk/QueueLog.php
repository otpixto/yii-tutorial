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
        return $this->belongsTo( 'App\Models\Asterisk\Cdr', 'callid', 'uniqueid' );
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
            $this->_operator = User
                ::whereHas( 'phoneSession', function ( $q )
                {
                    return $q
                        ->where( 'number', '=', $this->number() )
                        ->where( 'created_at', '<=', Carbon::parse( $this->cdr->calldate )->addSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() )
                        ->where( 'closed_at', '>=', Carbon::parse( $this->cdr->calldate )->subSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() );
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
