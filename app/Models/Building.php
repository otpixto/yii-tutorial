<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $name = str_replace( 'Московская обл., ', '', $this->name );
		if ( $withType && $this->buildingType )
		{
			$name .= ' (' . $this->buildingType->name . ')';
		}
		return $name;
    }

    public function getFullName ( $withNumber = true )
    {
        try
        {

            $segments = $this->getSegments();
            if ( $segments->count() )
            {
                $name = $segments->implode( 'name', ', ' );
                if ( $withNumber && $this->number )
                {
                    $name .= ', д.' . $this->number;
                }
            } else
            {
                $name = $this->name;
            }
            return $name;

        }
        catch ( \Exception $exception )
        {
            \Illuminate\Support\Facades\Log::error( $exception->getTraceAsString() );
        }
    }

    public function getSegments ( $break = false )
    {
        $current = $this->segment;
        $segments = collect();
        if ( ! $current ) return $segments;
		if ( $current->segmentType->is_visible )
		{
			$segments->push( $current );
		}
		$maxNum = 10;
		$num = 0;
        while ( $current->parent )
        {
            if ( $current->id == $current->parent->id || ( $num ++ ) >= $maxNum )
            {
                break;
            }
            else
            {
                $current = $current->parent;
                if ( $current->segmentType->is_visible )
                {
                    $segments->push( $current );
                }
                else if ( $break && $current->segmentType->break )
                {
                    break;
                }
            }
        }
        return $segments->reverse();
    }

    public static function create ( array $attributes = [] )
    {
        $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
        $building = self
			::mine()
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

    public function edit ( array $attributes = [], $updateCoordinates = false )
    {
        if ( ! empty( $attributes[ 'name' ] ) )
        {
            $attributes[ 'hash' ] = self::genHash( $attributes[ 'name' ] );
            $building = self
                ::mine()
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
        if ( $updateCoordinates || ( ( ! $building->lon || ! $building->lat ) && $building->lon != -1 && $building->lat != -1 ) )
        {
            $this->getCoordinates();
        }
        return $building;
    }

    public function getCoordinates ()
    {
        try
        {
            $url = 'https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode( $this->name ) . '&format=json';
            if ( $this->provider->yandex_key )
            {
                $url .= '&apikey=' . $this->provider->yandex_key;
            }
            $yandex = json_decode( file_get_contents( $url ) );
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
            dd( $e );
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

    public function searchData(Request $request)
    {
        $search = trim( $request->get( 'search', '' ) );
        $provider_id = $request->get( 'provider_id' );
        $segment_id = $request->get( 'segment_id' );
        $segment_name = $request->get( 'segment_name' );
        $parent_segment_name = $request->get( 'parent_segment_name' );
        $building_type_id = $request->get( 'building_type_id' );
        $management_id = $request->get( 'management_id' );

        $buildings = Building
            ::mine( Building::IGNORE_MANAGEMENT )
            ->orderBy( Building::$_table . '.name' );

        if ( ! empty( $segment_name ) )
        {
            $buildings
                ->whereHas( 'segment', function ( $q ) use ( $segment_name )
                {
                    $segment_parent_name = $segment_name;
                    if ( strpos( $segment_name, ',' ) )
                    {
                        $segmentsArray = explode( ',', $segment_name );
                        if ( count( $segmentsArray ) == 2 )
                        {
                            $segment_name = $segmentsArray[ 1 ];
                            $segment_parent_name = $segmentsArray[ 0 ];
                        }
                    }
                    $s = '%' . str_replace( ' ', '%', trim( $segment_name ) ) . '%';
                    return $q
                        ->where( Segment::$_table . '.name', 'like', $s )
                        ->orWhereHas( 'parent', function ( $q ) use ( $segment_parent_name )
                        {
                            $s = '%' . str_replace( ' ', '%', trim( $segment_parent_name ) ) . '%';
                            return $q
                                ->where( 'name', 'like', $s );
                        } );
                } );
        }

        if ( ! empty( $parent_segment_name ) )
        {
            $buildings
                ->whereHas( 'segment', function ( $q ) use ( $parent_segment_name )
                {
                    return $q
                        ->whereHas( 'parent', function ( $q ) use ( $parent_segment_name )
                        {
                            $s = '%' . str_replace( ' ', '%', trim( $parent_segment_name ) ) . '%';
                            return $q
                                ->where( 'name', 'like', $s );
                        } );
                } );
        }

        if ( ! empty( $provider_id ) )
        {
            $buildings
                ->where( Building::$_table . '.provider_id', '=', $provider_id );
        }

        if ( ! empty( $segment_id ) )
        {
            $buildings
                ->where( Building::$_table . '.segment_id', '=', $segment_id );
        }

        if ( ! empty( $building_type_id ) )
        {
            $buildings
                ->where( Building::$_table . '.building_type_id', '=', $building_type_id );
        }

        if ( ! empty( $management_id ) )
        {
            $buildings
                ->whereHas( 'managements', function ( $q ) use ( $management_id )
                {
                    return $q
                        ->where( Management::$_table . '.id', '=', $management_id );
                } );
        }

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $buildings
                ->where( Building::$_table . '.name', 'like', $s );
        }

        return $buildings;
    }

}
