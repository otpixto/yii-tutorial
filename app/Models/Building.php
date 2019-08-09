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
        'number',
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
        'mosreg_id',
    ];

    protected $fillable = [
        'provider_id',
        'segment_id',
        'building_type_id',
        'hash',
        'guid',
        'name',
        'number',
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
        'mosreg_id',
    ];

    public function managements ()
    {
        return $this->belongsToMany( Management::class, 'managements_buildings' );
    }

    public function segment ()
    {
        return $this->belongsTo( Segment::class );
    }

    public function buildingType ()
    {
        return $this->belongsTo( BuildingType::class );
    }

    public function customers ()
    {
        return $this->hasMany( Customer::class, 'id', 'actual_building_id' );
    }

    public function rooms ()
    {
        return $this->hasMany( BuildingRoom::class );
    }

    public function getAddress ( $withType = false )
    {
        //return $this->address . ' д. ' . $this->home;
        $name = $this->name;
		if ( $withType && $this->buildingType )
		{
			$name .= ' (' . $this->buildingType->name . ')';
		}
		return $name;
    }

    public function getFullName ( $withNumber = true )
    {
        $segments = $this->getSegments();
        if ( $segments->count() )
        {
            $name = $segments->implode( 'name', ', ' );
            if ( $withNumber && $this->number )
            {
                $name .= ', д.' . $this->number;
            }
        }
        else
        {
            $name = $this->name;
        }
        return $name;
    }

    public function getSegments ( $break = false )
    {
        $current = $this->segment;
        $segments = collect();
        if ( ! $current ) return $segments;
        $segments->push( $current );
        while ( $current->parent )
        {
            $current = $current->parent;
            $segments->push( $current );
            if ( $break && $current->segmentType->break )
            {
                break;
            }
        }
        return $segments->reverse();
    }

    public static function create ( array $attributes = [] )
    {
        $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
        $building = self
            ::where( 'provider_id', '=', $attributes[ 'provider_id' ] )
            ->where( 'hash', '=', $attributes[ 'hash' ] )
            ->first();
        if ( $building )
        {
            return new MessageBag( [ 'Такой адрес уже существует' ] );
        }
        $building = parent::create( $attributes );
        $building->getCoordinates();
        $building->save();
        return $building;
    }

    public function edit ( array $attributes = [] )
    {
        if ( ! empty( $attributes[ 'name' ] ) )
        {
            $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
            $building = self
                ::where( 'provider_id', '=', $attributes[ 'provider_id' ] )
                ->where( 'hash', '=', $attributes[ 'hash' ] )
                ->where( 'id', '!=', $this->id )
                ->first();
            if ( $building )
            {
                return new MessageBag( [ 'Такой адрес уже существует' ] );
            }
        }
        if ( ! empty( $attributes[ 'date_of_construction' ] ) )
        {
            $attributes[ 'date_of_construction' ] = Carbon::parse( $attributes[ 'date_of_construction' ] )->format( 'Y-m-d' );
        }
        $attributes[ 'is_first_floor_living' ] = ! empty( $attributes[ 'is_first_floor_living' ] ) ? 1 : 0;
        $building = parent::edit( $attributes );
        if ( ( ! $building->lon || ! $building->lat ) && $building->lon != -1 && $building->lat != -1 )
        {
            $this->getCoordinates();
        }
        return $building;
    }

    public function getCoordinates ()
    {
        try
        {
            $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $this->name ) ) );
            if ( isset( $yandex->response->GeoObjectCollection->featureMember[ 0 ] ) )
            {
                $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[ 0 ]->GeoObject->Point->pos );
                $this->lon = $pos[ 0 ];
                $this->lat = $pos[ 1 ];
            }
            else
            {
                $this->lon = -1;
                $this->lat = -1;
            }
            $this->save();
        }
        catch ( \Exception $e )
        {

        }
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
                ->mineProvider();
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
