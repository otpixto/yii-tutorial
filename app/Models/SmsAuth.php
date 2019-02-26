<?php

namespace App\Models;

class SmsAuth extends BaseModel
{

    protected $table = 'sms_auth';
    public static $_table = 'sms_auth';

    public static $name = 'Двухфакторная авторизация';

    protected $fillable = [
        'phone',
        'token',
        'code',
        'expired_at',
    ];

}
