<?php
declare ( strict_types = 1 );

namespace App\Models;

use App\Classes\Asterisk;
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
            $asterisk = new Asterisk();
            $number = $asterisk->prepareNumber( $this->number );
            $channel = $asterisk->prepareChannel( $number );
            $calls = Cdr
                ::answered()
                ->incoming()
                ->whereHas( 'queueLog', function ( $queueLog ) use ( $channel )
                {
                    return $queueLog
                        ->completed()
                        ->where( 'agent', '=', $channel )
                        ->where( 'time', '>=', $this->created_at->subSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() );
                    if ( $this->deleted_at )
                    {
                        $calls
                            ->where( 'time', '<=', $this->deleted_at->addSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() );
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