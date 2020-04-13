<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProviderToken extends Model
{

    protected $table = 'providers_tokens';
    public static $_table = 'providers_tokens';

    public static $name = 'Токены';

    protected $fillable = [
        'provider_key_id',
        'user_id',
        'token',
        'http_user_agent',
        'ip',
    ];

    public function providerKey ()
    {
        return $this->belongsTo( ProviderKey::class );
    }

    public function user ()
    {
        return $this->belongsTo( User::class );
    }

    public static function create ( array $attributes = [] )
    {
        $providerToken = ProviderToken
            ::where( 'provider_key_id', '=', $attributes[ 'provider_key_id' ] )
            ->where( 'user_id', '=', $attributes[ 'user_id' ] )
            ->where( 'http_user_agent', '=', $attributes[ 'http_user_agent' ] )
            ->where( 'ip', '=', $attributes[ 'ip' ] )
            ->first();
        if ( ! $providerToken )
        {
            $providerToken = new self( $attributes );
        }
        else
        {
            $providerToken->updated_at = Carbon::now()->toDateTimeString();
        }
        $providerToken->save();
        return $providerToken;
    }

}
