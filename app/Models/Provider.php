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
        'need_act',
    ];

    public function phones ()
    {
        return $this->hasMany( 'App\Models\ProviderPhone' );
    }

    public function buildings ()
    {
        return $this->hasMany( 'App\Models\Building' );
    }

    public function segments ()
    {
        return $this->hasMany( 'App\Models\Segment' );
    }

    public function groups ()
    {
        return $this->hasMany( 'App\Models\Group' );
    }

    public function managements ()
    {
        return $this->hasMany( 'App\Models\Management' );
    }

    public function types ()
    {
        return $this->hasMany( 'App\Models\Type' );
    }

    public function customers ()
    {
        return $this->hasMany( 'App\Models\Customer' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_providers' );
    }

    public function phoneSessions ()
    {
        return $this->hasMany( 'App\Models\PhoneSession' )
            ->notClosed();
    }

    public function scopeMine ( $query, User $user = null )
    {
        if ( ! $user ) $user = \Auth::user();
        if ( ! $user ) return false;
        if ( ! self::subDomainIs( 'operator', 'system' ) )
        {
            $query
                ->whereIn( self::getTable() . '.id', $user->providers()->pluck( self::getTable() . '.id' ) );
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        if ( ! self::subDomainIs( 'operator', 'system' ) )
        {
            $query
                ->where( 'domain', '=', \Request::getHost() );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        if ( ! isset( $attributes[ 'need_act' ] ) )
        {
            $attributes[ 'need_act' ] = 0;
        }
        $provider = parent::create( $attributes );
        return $provider;
    }

    public function edit ( array $attributes = [] )
    {
        if ( ! isset( $attributes[ 'need_act' ] ) )
        {
            $attributes[ 'need_act' ] = 0;
        }
        $provider = parent::edit( $attributes );
        return $provider;
    }

    public function addPhone ( array $attributes = [] )
    {
        self::normalizeValues( $attributes );
        $old = ProviderPhone::where( 'phone', '=', $attributes[ 'phone' ] )->first();
        if ( $old )
        {
            return new MessageBag( [ 'Номер "' . $old->phone . '" уже есть в базе данных' ] );
        }
        $attributes[ 'provider_id' ] = $this->id;
        $providerPhone = ProviderPhone::create( $attributes );
        if ( $providerPhone instanceof MessageBag )
        {
            return $providerPhone;
        }
        $this->addLog( 'Добавлен телефон "' . $providerPhone->phone . '"' );
        $providerPhone->save();
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
        if ( self::getCurrent() && self::$current->logo  )
        {
            return '/storage/' . self::$current->logo;
        }
        else
        {
            return self::getDefaultLogo();
        }
    }

    public function getUrl ()
    {
        $url = $this->ssl ? 'https://' : 'http://';
        $url .= $this->domain;
        return $url;
    }

    public static function getDefaultLogo ()
    {
        return '/storage/logo/default.png';
    }

}
