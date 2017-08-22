<?php

namespace App\Models;

class ManagementSubscription extends BaseModel
{

    protected $table = 'managements_subscriptions';

    public static $rules = [
        'management_id'         => 'required|integer',
        'telegram_id'           => 'required|integer',
    ];

    protected $fillable = [
        'management_id',
        'telegram_id',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

}
