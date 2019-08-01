<?php

namespace App\Models;

class GzhiApiProvider extends BaseModel
{

    const GJI_SOAP_URL = 'https://next-lk.eiasmo.ru/eds-service/';

    public $table = 'gzhi_api_providers';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'org_guid',
        'login',
        'password'
    ];

}
