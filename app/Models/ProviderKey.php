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
        'referer',
        'token_life',
        'maxAttempts',
        'decayMinutes',
    ];

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function providerTokens ()
    {
        return $this->hasMany( ProviderToken::class );
    }
	
	public static function create ( array $attributes = [] )
    {
        if ( isset( $attributes[ 'ip' ] ) )
        {
            $attributes[ 'ip' ] = trim( str_replace( [ 'http://', 'https://', ',', ';', ' ', "\n\n" ], "\n", $attributes[ 'ip' ] ) ) . "\n";
        }
		if ( isset( $attributes[ 'referer' ] ) )
        {
            $attributes[ 'referer' ] = trim( str_replace( [ 'http://', 'https://', ',', ';', ' ', "\n\n" ], "\n", $attributes[ 'referer' ] ) ) . "\n";
        }
        $provider = parent::create( $attributes );
        return $provider;
    }

    public function edit ( array $attributes = [] )
    {
        if ( isset( $attributes[ 'ip' ] ) )
        {
            $attributes[ 'ip' ] = trim( str_replace( [ 'http://', 'https://', ',', ';', ' ', "\n\n" ], "\n", $attributes[ 'ip' ] ) ) . "\n";
        }
		if ( isset( $attributes[ 'referer' ] ) )
        {
            $attributes[ 'referer' ] = trim( str_replace( [ 'http://', 'https://', ',', ';', ' ', "\n\n" ], "\n", $attributes[ 'referer' ] ) ) . "\n";
        }
        $provider = parent::edit( $attributes );
        return $provider;
    }

}
