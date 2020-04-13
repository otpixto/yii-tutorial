<?php

namespace App\Models;

class Vendor extends BaseModel
{
    const GZHI_VENDOR_ID = 1;
    const DOBRODEL_VENDOR_ID = 2;
    const STATEMENT_VENDOR_ID = 3;
    const EAIS_VENDOR_ID = 4;
    const EDS_VENDOR_ID = 5;
    const ECUR_VENDOR_ID = 6;
    const DEFAULT_VENDOR_ID = 7;

    protected $table = 'vendors';
    public static $_table = 'vendors';

    public static $name = 'Сторонние сервисы';

    protected $fillable = [
        'name',
    ];

}
