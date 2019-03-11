<?php

namespace App\Models;

class ProviderContext extends BaseModel
{

    protected $table = 'providers_contexts';
    public static $_table = 'providers_contexts';

    public static $name = 'Контексты астериска';

    protected $fillable = [
        'provider_id',
        'context',
    ];

}
