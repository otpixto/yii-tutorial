<?php

namespace App\Console\Commands;

use App\Classes\Segments;
use App\Models\Building;
use App\Models\Segment;
use App\Models\SegmentType;
use Illuminate\Console\Command;

class CreateSegments extends Command
{

    protected $signature = 'create:segments';

    public function handle ()
    {

        try
        {
            $handle = fopen( public_path( 'files/x247.csv' ), 'r' );
            $firstIgnore = false;
            \DB::beginTransaction();
            $provider_id = 9;
            while ( $row = fgetcsv( $handle, 1000, ',' ) )
            {
                if ( ! $firstIgnore )
                {
                    $firstIgnore = true;
                    continue;
                }
                $building_name = array_shift( $row );
                $building = Building
                    ::where( 'provider_id', '=', $provider_id )
                    ->where( 'name', '=', $building_name )
                    ->first();
                if ( ! $building )
                {
                    $exp = explode( ',', $building_name );
                    $number = trim( str_replace( 'д.', '', end( $exp ) ) );
                    $building = Building::create([
                        'provider_id' => 9,
                        'name' => $building_name,
                        'number' => $number,
                        'building_type_id' => 1,
                    ]);
                }
                $arr = array_chunk( $row, 2 );
                $parent_id = null;
                foreach ( $arr as $i => $cell )
                {
                    $segmentType = SegmentType
                        ::where( 'name', '=', trim( $cell[ 0 ] ) )
                        ->first();
                    if ( $segmentType )
                    {
                        $segment = Segment
                            ::where( 'provider_id', '=', $building->provider_id )
                            ->where( 'name', '=', trim( $cell[ 1 ] ) )
                            ->where( 'segment_type_id', '=', $segmentType->id )
                            ->first();
                        if ( ! $segment )
                        {
                            $segment = Segment::create([
                                'name'                  => trim( $cell[ 1 ] ),
                                'parent_id'             => $parent_id,
                                'provider_id'           => $building->provider_id,
                                'segment_type_id'       => $segmentType->id,
                            ]);
                            $segment->save();
                        }
                        $parent_id = $segment->id;
                    }
                }
                $building->segment_id = $segment->id;
                $building->save();
            }

            \DB::commit();
        }
        catch ( \Exception $e )
        {
            \DB::rollback();
            dd( $e );
        }

        die;

        $handle = fopen( public_path( 'files/ram_segments.csv' ), 'r' );
        while ( $row = fgetcsv( $handle, 1000, ',' ) )
        {
            $building_id = array_shift( $row );
            $building = Building::find( $building_id );
            if ( ! $building ) continue;
            $arr = array_chunk( $row, 2 );
            $parent_id = null;
            foreach ( $arr as $i => $cell )
            {
                $segmentType = SegmentType
                    ::where( 'name', '=', trim( $cell[ 0 ] ) )
                    ->first();
                if ( $segmentType )
                {
                    $segment = Segment
                        ::where( 'name', '=', trim( $cell[ 1 ] ) )
                        ->where( 'segment_type_id', '=', $segmentType->id )
                        ->first();
                    if ( ! $segment )
                    {
                        $segment = Segment::create([
                            'name'                  => trim( $cell[ 1 ] ),
                            'parent_id'             => $parent_id,
                            'provider_id'           => 1,
                            'segment_type_id'       => $segmentType->id,
                        ]);
                        $segment->save();
                    }
                    $parent_id = $segment->id;
                }
            }
            $building->segment_id = $segment->id;
            $building->save();
        }

        /*$handle = fopen( public_path( 'files/pushkino.csv' ), 'r' );
        while ( $row = fgetcsv( $handle, 1000, ',' ) )
        {
            $building_name = trim( array_shift( $row ) );
            $building = Building
                ::where( 'name', '=', $building_name )
                ->first();
            if ( ! $building )
            {
                $building = Building::create([
                    'provider_id'           => 1,
                    'building_type_id'      => 1,
                    'name'                  => $building_name,
                ]);
                $building->save();
            }
            $arr = array_chunk( $row, 2 );
            $parent_id = null;
            foreach ( $arr as $i => $cell )
            {
                $segmentType = SegmentType
                    ::where( 'name', '=', trim( $cell[ 0 ] ) )
                    ->first();
                if ( $segmentType )
                {
                    $segment = Segment
                        ::where( 'name', '=', trim( $cell[ 1 ] ) )
                        ->where( 'segment_type_id', '=', $segmentType->id )
                        ->first();
                    if ( ! $segment )
                    {
                        $segment = Segment::create([
                            'name'                  => trim( $cell[ 1 ] ),
                            'parent_id'             => $parent_id,
                            'provider_id'           => 1,
                            'segment_type_id'       => $segmentType->id,
                        ]);
                        $segment->save();
                    }
                    $parent_id = $segment->id;
                }
            }
            $building->segment_id = $segment->id;
            $building->save();
        }*/

        Segments::clearCache();

    }

}