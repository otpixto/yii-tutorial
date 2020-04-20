<?php

namespace App\Models;

use App\Jobs\SendTelegram;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ManagementSubscription extends BaseModel
{

    use DispatchesJobs;

    protected $table = 'managements_subscriptions';
    public static $_table = 'managements_subscriptions';

    public static $name = 'Подписка на оповещения';

    public static $rules = [
        'management_id'         => 'required|integer',
        'telegram_id'           => 'required|integer',
        'first_name'            => 'nullable|integer',
        'last_name'             => 'nullable|integer',
        'username'              => 'required|string',
    ];

    protected $nullable = [
        'first_name',
        'last_name',
    ];

    protected $fillable = [
        'management_id',
        'telegram_id',
        'first_name',
        'last_name',
        'username',
    ];

    public function management ()
    {
        return $this->belongsTo( Management::class );
    }

    public function getName ()
    {
        $name = '';
        if ( ! empty( $this->first_name ) )
        {
            $name .= ' ' . $this->first_name;
        }
        if ( ! empty( $this->last_name ) )
        {
            $name .= ' ' . $this->last_name;
        }
        return trim( $name );
    }

    public function sendTelegram ( $message = null )
    {
        if ( ! $message ) return;
        $this->dispatch( new SendTelegram( $this, $message ) );
        return true;
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'management', function ( $management )
            {
                return $management
                    ->mine();
            });
    }

}
