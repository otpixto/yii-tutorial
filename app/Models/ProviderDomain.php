<?php

namespace App\Models;

class ProviderDomain extends BaseModel
{

    protected $table = 'providers_domains';
    public static $_table = 'providers_domains';

    public static $name = 'Домены поставщика';

    protected $fillable = [
        'provider_id',
        'domain',
    ];

}
