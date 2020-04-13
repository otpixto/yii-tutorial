<?php

namespace App\Models;

class UserPosition extends BaseModel
{

    protected $table = 'users_positions';
    public static $_table = 'users_positions';

    public static $name = 'Геопозиция пользователя';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'position_at',
    ];

    protected $fillable = [
        'user_id',
        'lon',
        'lat',
        'position_at',
    ];

}
