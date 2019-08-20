<?php

namespace App\Models;

use App\Classes\Asterisk;
use App\Classes\GzhiConfig;
use App\User;
use Illuminate\Support\MessageBag;
use Webpatser\Uuid\Uuid;

class Provider extends BaseModel
{

    protected $table = 'providers';
    public static $_table = 'providers';

    public static $name = 'Поставщик';

    public static $current = null;

    private $asterisk = null;

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
        'sms_auth',
    ];

    public function phones ()
    {
        return $this->hasMany( ProviderPhone::class );
    }

    public function contexts ()
    {
        return $this->hasMany( ProviderContext::class );
    }

    public function providerKeys ()
    {
        return $this->hasMany( ProviderKey::class );
    }

    public function buildings ()
    {
        return $this->hasMany( Building::class );
    }

    public function segments ()
    {
        return $this->hasMany( Segment::class );
    }

    public function buildingsGroups ()
    {
        return $this->hasMany( BuildingGroup::class );
    }

    public function typesGroups ()
    {
        return $this->hasMany( TypeGroup::class );
    }

    public function managements ()
    {
        return $this->hasMany( Management::class );
    }

    public function types ()
    {
        return $this->hasMany( Type::class );
    }

    public function customers ()
    {
        return $this->hasMany( Customer::class );
    }

    public function users ()
    {
        return $this->belongsToMany( User::class, 'users_providers' );
    }

    public function phoneSessions ()
    {
        return $this->hasMany( PhoneSession::class )
            ->notClosed();
    }

    public function scopeMine ( $query, User $user = null )
    {
        if ( ! $user ) $user = \Auth::user();
        if ( $user )
        {
            $query
                ->where( function ( $q ) use ( $user )
                {
                    return $q
                        ->where( self::getTable() . '.id', '=', $user->provider_id )
                        ->orWhereIn( self::getTable() . '.id', $user->providers()->pluck( self::getTable() . '.id' ) );
                });
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        return $query
            ->where( 'domain', '=', self::$current ? self::$current->domain : \Request::getHost() );
    }

    public static function create ( array $attributes = [] )
    {
        if ( ! isset( $attributes[ 'need_act' ] ) )
        {
            $attributes[ 'need_act' ] = 0;
        }
        if ( ! isset( $attributes[ 'sms_auth' ] ) )
        {
            $attributes[ 'sms_auth' ] = 0;
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
        if ( ! isset( $attributes[ 'sms_auth' ] ) )
        {
            $attributes[ 'sms_auth' ] = 0;
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

    public function addKey ( array $attributes = [] )
    {
        self::normalizeValues( $attributes );
        $attributes[ 'provider_id' ] = $this->id;
        $attributes[ 'api_key' ] = Uuid::generate();;
        $providerKey = ProviderKey::create( $attributes );
        if ( $providerKey instanceof MessageBag )
        {
            return $providerKey;
        }
        $this->addLog( 'Добавлен ключ "' . $providerKey->api_key . '" (' . $providerKey->description . ')' );
        $providerKey->save();
        return $providerKey;
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
        if ( ! self::$current )
        {
            self::setCurrent( self::current()->first() );
        }
        return self::$current;
    }
	
	public static function setCurrent ( Provider $provider = null )
	{
		self::$current = $provider;
	}

    public function getGzhiConfig ()
    {
        return new GzhiConfig( $this );
    }

    public function getAsteriskConfig ()
    {
        $config = config( 'asterisk' );
        $config[ 'queue' ] = $this->queue;
        $config[ 'ip' ] = $this->asterisk_ip;
        $config[ 'external_ip' ] = $this->asterisk_external_ip;
        $config[ 'incoming_context' ] = $this->incoming_context;
        $config[ 'outgoing_context' ] = $this->outgoing_context;
        $config[ 'autodial_context' ] = $this->autodial_context;
        $config[ 'channel_mask' ] = $this->channel_mask;
        $config[ 'channel_prefix' ] = $this->channel_prefix;
        $config[ 'channel_postfix' ] = $this->channel_postfix;
        $config[ 'channel_postfix_trunc' ] = $this->channel_postfix_trunc;
        return $config;
    }

    public function getAsterisk ( $flush = false ) : Asterisk
    {
        if ( $flush || is_null( $this->asterisk ) )
        {
            $this->asterisk = new Asterisk( $this->getAsteriskConfig() );
        }
        return $this->asterisk;
    }

    public static function getLogo ()
    {
        if ( self::getCurrent() && self::getCurrent()->logo  )
        {
            return '/storage/' . self::getCurrent()->logo;
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
