<?php

namespace App\Models;

use App\Classes\GzhiConfig;
use App\User;
use Illuminate\Support\MessageBag;

class Region extends BaseModel
{

    protected $table = 'regions';
    public static $_table = 'regions';

    public static $name = 'Регион';

    public static $current_region = null;

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
        return $this->hasMany( 'App\Models\RegionPhone' );
    }

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'regions_addresses' );
    }

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'regions_managements' );
    }

    public function customers ()
    {
        return $this->hasMany( 'App\Models\Customer' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_regions' );
    }

    public function scopeMine ( $query, User $user = null )
    {
        if ( ! $user ) $user = \Auth::user();
        if ( ! $user ) return false;
        if ( ! self::subDomainIs( 'operator', 'system' ) || ! $user->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereIn( self::$_table . '.id', $user->regions()->pluck( Region::$_table . '.id' ) );
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
        $validator = \Validator::make( $attributes, RegionPhone::$rules );
        if ( $validator->fails() ) return $validator;
        $regionPhone = RegionPhone::create( $attributes );
        if ( $regionPhone instanceof MessageBag )
        {
            return $regionPhone;
        }
        $this->addLog( 'Добавлен телефон "' . $phone . '"' );
        return $regionPhone;
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
        self::$current_region = ! self::isOperatorUrl() ? self::current()->first() : null;
        return self::$current_region;
    }

    public function getGzhiConfig ()
    {
        return new GzhiConfig( $this );
    }

}
