<?php
declare ( strict_types = 1 );

namespace App\Models;

use App\Models\Asterisk\Cdr;

class PhoneSession extends BaseModel
{

    protected $table = 'phone_sessions';

    private $_calls = null;
    private $_limit = null;

    public static $name = 'Телефонная сессия';

    public static $rules = [
        'user_id'       => 'required|integer|unique:phone_sessions,user_id,NULL,id,deleted_at,NULL',
        'number'        => 'required|string|min:2'
    ];

    protected $fillable = [ 'user_id', 'number' ];

    public function user ()
    {
        return $this->belongsTo ( 'App\User' );
    }

    public function calls ( $limit = null )
    {
        if ( is_null( $this->_calls ) || $this->_limit != $limit )
        {
            $calls = Cdr
                ::answered()
                ->incoming()
                ->whereHas( 'queueLog', function ( $queueLog )
                {
                    return $queueLog
                        ->completed()
                        ->where( 'agent', '=', 'SIP/' . $this->number )
                        ->where( 'time', '>=', $this->created_at->subSeconds( 10 )->toDateTimeString() );
                    if ( $this->deleted_at )
                    {
                        $calls
                            ->where( 'time', '<=', $this->deleted_at->addSeconds( 10 )->toDateTimeString() );
                    }
                });
            if ( $limit )
            {
                $calls->take( $limit );
            }
            $this->_calls = $calls->get();
        }
        return $this->_calls;
    }

}