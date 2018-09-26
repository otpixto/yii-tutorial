<?php

namespace App\Models;

class Vendor extends BaseModel
{

    protected $table = 'vendors';
    public static $_table = 'vendors';

    public static $name = 'Сторонние сервисы';

    protected $fillable = [
        'name',
    ];

}
