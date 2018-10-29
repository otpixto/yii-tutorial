<?php

namespace App\Models;

class UserPosition extends BaseModel
{

    protected $table = 'users_positions';
    public static $_table = 'users_positions';

    public static $name = 'Геопозиция пользователя';

    protected $fillable = [
        'user_id',
        'lon',
        'lat',
    ];

    public function user ()
    {
        return $this->belongsTo( 'App\User' );
    }

}
