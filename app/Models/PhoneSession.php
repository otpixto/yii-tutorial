<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;
use App\User;
use Carbon\Carbon;

class PhoneSession extends BaseModel
{

    protected $table = 'phone_sessions';
    public static $_table = 'phone_sessions';

    protected $dates = [
        'closed_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    private $_calls = null;
    private $_limit = null;

    public static $name = 'Телефонная сессия';

    protected $fillable = [
        'provider_id',
        'user_id',
        'number',
        'channel',
        'queue',
    ];

    public function user ()
    {
        return $this->belongsTo ( User::class );
    }

    public function calls ( $limit = null )
    {
        if ( is_null( $this->_calls ) || $this->_limit != $limit )
        {
            $asterisk = Provider::getCurrent()->getAsterisk();
            $channel = $this->channel ?: $asterisk->prepareChannel( $this->number );
            $calls = Cdr
                ::answered()
                ->incoming()
                ->where( 'calldate', '>=', $this->created_at->subSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() )
                ->whereHas( 'queueLogs', function ( $queueLogs ) use ( $channel )
                {
                    return $queueLogs
                        ->completed()
                        ->where( 'agent', '=', $channel );
                });
            if ( $this->deleted_at )
            {
                $calls
                    ->where( 'calldate', '<=', $this->deleted_at->addSeconds( \Config::get( 'asterisk.tolerance' ) )->toDateTimeString() );
            }
            if ( $limit )
            {
                $calls->take( $limit );
            }
            $this->_calls = $calls->paginate(5)->get();
        }
        return $this->_calls;
    }
	
	public function scopeNotClosed ( $query )
	{
		return $query
			->whereNull( 'closed_at' );
	}

	public function scopeMine ( $query )
    {
        return $query
            ->mineProvider()
            ->whereHas( 'user', function ( $user )
            {
                return $user
                    ->mine();
            });
    }

    public function close ()
    {
        $this->closed_at = Carbon::now()->toDateTimeString();
        $this->save();
    }

}
