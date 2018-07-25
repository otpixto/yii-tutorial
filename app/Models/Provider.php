<?php

namespace App\Models;

use App\Classes\GzhiConfig;
use App\User;
use Illuminate\Support\MessageBag;

class Provider extends BaseModel
{

    protected $table = 'providers';
    public static $_table = 'providers';

    public static $name = 'Поставщик';

    public static $current = null;

    protected $nullable = [
        'guid',
        'username',
        'password',
    ];

    protected $fillable = [
        'guid',
        'username',
        'password',
        'name',
        'domain',
    ];

    public function phones ()
    {
        return $this->hasMany( 'App\Models\ProviderPhone' );
    }

    public function buildings ()
    {
        return $this->belongsToMany( 'App\Models\Building', 'providers_buildings' );
    }

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'providers_managements' );
    }

    public function types ()
    {
        return $this->belongsToMany( 'App\Models\Type', 'providers_types' );
    }

    public function customers ()
    {
        return $this->hasMany( 'App\Models\Customer' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_providers' );
    }

    public function scopeMine ( $query, User $user = null )
    {
        if ( ! $user ) $user = \Auth::user();
        if ( ! $user ) return false;
        if ( ! self::subDomainIs( 'operator', 'system' ) || ! $user->can( 'supervisor.all_providers' ) )
        {
            $query
                ->whereIn( self::$_table . '.id', $user->providers()->pluck( Provider::$_table . '.id' ) );
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        if ( ! self::isOperatorUrl() )
        {
            $query
                ->where( self::$_table . '.domain', '=', \Request::getHost() );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        $region = parent::create( $attributes );
        return $region;
    }

    public function edit ( array $attributes = [] )
    {
        $region = parent::edit( $attributes );
        return $region;
    }

    public function addPhone ( $phone )
    {
        $attributes = [
            'region_id'     => $this->id,
            'phone'         => $phone
        ];
        $validator = \Validator::make( $attributes, ProviderPhone::$rules );
        if ( $validator->fails() ) return $validator;
        $providerPhone = ProviderPhone::create( $attributes );
        if ( $providerPhone instanceof MessageBag )
        {
            return $providerPhone;
        }
        $this->addLog( 'Добавлен телефон "' . $phone . '"' );
        return $providerPhone;
    }

    public static function isOperatorUrl ()
    {
        return self::subDomainIs( 'operator' );
    }

    public static function isSystemUrl ()
    {
        return self::subDomainIs( 'system' );
    }

    public static function getSubDomain ()
    {
        $exp = explode( '.', \Request::getHost() );
        if ( count( $exp ) < 3 ) return null;
        return $exp[ 0 ];
    }

    public static function subDomainIs ( ... $subDomains )
    {
        $subDomain = self::getSubDomain();
        foreach ( $subDomains as $_subDomain )
        {
            if ( $subDomain == config( 'domain.' . $_subDomain, $_subDomain ) )
            {
                return true;
            }
        }
        return false;
    }

    public static function getCurrent ()
    {
        self::$current = ! self::isOperatorUrl() ? self::current()->first() : null;
        return self::$current;
    }

    public function getGzhiConfig ()
    {
        return new GzhiConfig( $this );
    }

    public static function getLogo ()
    {
        if ( self::getCurrent() && self::$current->logo )
        {
            return '/storage/' . self::$current->logo;
        }
        else
        {
            return '/storage/logo/default.png';
        }
    }

}
