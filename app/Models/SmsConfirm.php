<?php

namespace App\Models;

class SmsConfirm extends BaseModel
{

    protected $table = 'sms_confirm';
    public static $_table = 'sms_confirm';

    public static $name = 'Подтверждение телефона';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expired_at',
    ];

    protected $fillable = [
        'phone',
        'code',
        'expired_at',
    ];

}
