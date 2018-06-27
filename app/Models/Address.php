<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class Address extends BaseModel
{

    protected $table = 'addresses';
    public static $_table = 'addresses';

    public static $name = 'Адрес';

    protected $nullable = [
        'hash',
        'guid',
        'lon',
        'lat',
    ];

    protected $fillable = [
        'hash',
        'guid',
        'name',
        'lon',
        'lat',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_addresses' );
    }

    public function region ()
    {
        return $this->belongsTo( 'App\Models\Region' );
    }

    public function regions ()
    {
        return $this->belongsToMany( 'App\Models\Region', 'regions_addresses' );
    }

    public function customers ()
    {
        return $this->hasMany( 'App\Models\Customer', 'id', 'actual_address_id' );
    }

    public function getAddress ()
    {
        //return $this->address . ' д. ' . $this->home;
        return $this->name;
    }

    public static function create ( array $attributes = [] )
    {
        $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
        $address = self::where( 'hash', '=', $attributes[ 'hash' ] )->first();
        if ( $address )
        {
            return new MessageBag( [ 'Такой адрес уже существует' ] );
        }
        $address = parent::create( $attributes );
        return $address;
    }

    public function edit ( array $attributes = [] )
    {
        $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
        $address = self
            ::where( 'hash', '=', $attributes[ 'hash' ] )
            ->where( 'id', '!=', $this->id )
            ->first();
        if ( $address )
        {
            return new MessageBag( [ 'Такой адрес уже существует' ] );
        }
        return parent::edit( $attributes );
    }

    public function scopeMine ( $query, ... $flags )
    {
        if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) && ! \Auth::user()->can( 'supervisor.all_addresses' ) )
        {
            $query
                ->whereHas( 'managements', function ( $management )
                {
                    return $management
                        ->mine();
                });
        }
        if ( ! in_array( self::IGNORE_REGION, $flags ) && ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereHas( 'regions', function ( $q )
                {
                    return $q
                        ->mine()
                        ->current();
                });
        }
        return $query;
    }

    public function scopeSearch ( $query, $name )
    {
        return $query
            ->where( 'hash', '=', self::genHash( $name ) );
    }

}
