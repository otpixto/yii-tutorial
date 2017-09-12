<?php

namespace App\Models\Asterisk;

use App\User;

class QueueLog extends BaseModel
{

    protected $table = 'queue_log';

    private $_operator = -1;

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

    public function ext_number ()
    {
        return mb_substr( $this->agent, -2 );
    }

    public function operator ()
    {
        if ( $this->_operator == -1 )
        {
            $this->_operator = User
                ::whereHas( 'phoneSession', function ( $q )
                {
                    return $q
                        ->withTrashed()
                        ->where( 'ext_number', '=', $this->ext_number() )
                        ->where( 'created_at', '>=', $this->time )
                        ->where( 'deleted_at', '<=', $this->time );
                })
                ->first();
        }
        return $this->_operator;
    }

}
