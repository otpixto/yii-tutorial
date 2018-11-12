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

        $handle = fopen( public_path( 'files/juk_segments.csv' ), 'r' );
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