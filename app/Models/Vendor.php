<?php

namespace App\Models;

class Vendor extends BaseModel
{
    const GZHI_VENDOR_ID = 1;
    const EAIS_VENDOR_ID = 4;

    protected $table = 'vendors';
    public static $_table = 'vendors';

    public static $name = 'Сторонние сервисы';

    protected $fillable = [
        'name',
    ];

}
