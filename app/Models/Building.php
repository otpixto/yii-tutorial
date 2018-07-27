<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;

class Building extends BaseModel
{

    protected $table = 'buildings';
    public static $_table = 'buildings';

    public static $name = 'Адрес';

    protected $nullable = [
        'hash',
        'guid',
        'lon',
        'lat',
        'date_of_construction',
        'eirts_number',
        'total_area',
        'living_area',
        'room_mask',
        'porches_count',
        'floor_count',
        'room_total_count',
        'first_floor_index',
        'is_first_floor_living',
    ];

    protected $fillable = [
        'provider_id',
        'segment_id',
        'hash',
        'guid',
        'name',
        'lon',
        'lat',
        'date_of_construction',
        'eirts_number',
        'total_area',
        'living_area',
        'room_mask',
        'porches_count',
        'floor_count',
        'room_total_count',
        'first_floor_index',
        'is_first_floor_living',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_buildings' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function segment ()
    {
        return $this->belongsTo( 'App\Models\Segment' );
    }

    public function buildingType ()
    {
        return $this->belongsTo( 'App\Models\BuildingType' );
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

    public function getFullName ( $withHome = true )
    {
        $segments = $this->getSegments();
        $name = $segments->implode( 'name', ', ' );
        if ( $withHome && $this->home )
        {
            $name .= ', д.' . $this->home;
        }
        return $name;
    }

    public function getSegments ()
    {
        $current = $this->segment;
        $segments = collect();
        if ( ! $current ) return $segments;
        $segments->push( $current );
        while ( $current->parent )
        {
            $current = $current->parent;
            $segments->push( $current );
        }
        return $segments->reverse();
    }

    public static function create ( array $attributes = [] )
    {
        $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
        $address = self
            ::where( 'provider_id', '=', $attributes[ 'provider_id' ] )
            ->where( 'hash', '=', $attributes[ 'hash' ] )
            ->first();
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
        if ( ! empty( $attributes[ 'date_of_construction' ] ) )
        {
            $attributes[ 'date_of_construction' ] = Carbon::parse( $attributes[ 'date_of_construction' ] )->format( 'Y-m-d' );
        }
        $attributes[ 'is_first_floor_living' ] = ! empty( $attributes[ 'is_first_floor_living' ] ) ? 1 : 0;
        $address = self
            ::where( 'provider_id', '=', $attributes[ 'provider_id' ] )
            ->where( 'hash', '=', $attributes[ 'hash' ] )
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
        if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) )
        {
            $query
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                });
        }
        if ( ! in_array( self::IGNORE_PROVIDER, $flags ) )
        {
            $query
                ->whereHas( 'provider', function ( $provider )
                {
                    return $provider
                        ->mine()
                        ->current();
                });
        }
        return $query;
    }

    public function scopeSearch ( $query, $name, $provider_id = null )
    {
        if ( ! $provider_id )
        {
            $provider_id = Provider::getCurrent() ? Provider::$current->id : null;
        }
        if ( $provider_id )
        {
            $query
                ->where( 'provider_id', '=', $provider_id );
        }
        return $query
            ->where( 'hash', '=', self::genHash( $name ) );
    }

}