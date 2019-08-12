<?php

namespace App\Models;

class GzhiApiProvider extends BaseModel
{

    public $table = 'gzhi_api_providers';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'org_guid',
        'login',
        'password'
    ];
}
