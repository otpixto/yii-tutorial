<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class Region extends BaseModel
{

    protected $table = 'regions';

    public static $name = 'Регион';

    public static $current_region = null;

    public static $rules = [
        'name'                  => 'required|string|max:255',
        'domain'                => 'required|string|max:100'
    ];

    public static $rules_phone = [
        'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
    ];

    protected $fillable = [
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
        if ( \Request::getHost() != \Session::get( 'settings' )->operator_domain || ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereIn( 'id', \Auth::user()->regions->pluck( 'id' ) );
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        if ( \Request::getHost() != \Session::get( 'settings' )->operator_domain )
        {
            $query
                ->where( 'domain', '=', \Request::getHost() );
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

}
