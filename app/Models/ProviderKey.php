<?php

namespace App\Models;

class ProviderKey extends BaseModel
{

    protected $table = 'providers_keys';
    public static $_table = 'providers_keys';

    public static $name = 'Ключ доступа';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'active_at',
    ];

    protected $fillable = [
        'provider_id',
        'api_key',
        'description',
        'ip',
        'token_life',
    ];

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function providerTokens ()
    {
        return $this->hasMany( ProviderToken::class );
    }

}
