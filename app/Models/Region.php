<?php

namespace App\Models;

use App\Classes\GzhiConfig;
use Illuminate\Support\MessageBag;

class Region extends BaseModel
{

    protected $table = 'regions';

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
        return $this->hasMany( 'App\Models\Address' );
    }

    public function managements ()
    {
        return $this->hasMany( 'App\Models\Management' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_regions' );
    }

    public function scopeMine ( $query )
    {
        if ( ! \Auth::user() ) return false;
        if ( ! Region::isOperatorUrl() || ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereIn( $this->getTable() . '.id', \Auth::user()->regions->pluck( 'id' ) );
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        if ( ! self::isOperatorUrl() )
        {
            $query
                ->where( $this->getTable() . '.domain', '=', \Request::getHost() );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        $region = parent::create( $attributes );
        if ( !empty( $attributes['phone'] ) )
        {
            $phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
            $res = $region->addPhone( $phone );
            if ( $res instanceof MessageBag )
            {
                return $res;
            }
        }
        return $region;
    }

    public function edit ( array $attributes = [] )
    {
        $region = parent::edit( $attributes );
        if ( !empty( $attributes['phone'] ) )
        {
            $phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
            $res = $region->addPhone( $phone );
            if ( $res instanceof MessageBag )
            {
                return $res;
            }
            $res->save();
        }
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
        return ( \Request::getHost() == \Session::get( 'settings' )->operator_domain );
    }

    public static function isSystemUrl ()
    {
        return ( \Request::getHost() == \Session::get( 'settings' )->system_domain );
    }

    public static function isNewsUrl ()
    {
        return ( \Request::getHost() == \Session::get( 'settings' )->news_domain );
    }

    public static function getCurrent ()
    {
        return self::current()->first();
    }

    public function getGzhiConfig ()
    {
        return new GzhiConfig( $this );
    }

}
