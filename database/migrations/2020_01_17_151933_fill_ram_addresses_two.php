<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillRamAddressesTwo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $array = array();
        for($i = 5; $i<=130; $i+=5)
        {
            $fileName = 'files/ram_addr_for_upload' . $i . '.csv';

            $csvData = \Illuminate\Support\Facades\File::get( storage_path( $fileName ) );

            $lines = explode( PHP_EOL, $csvData );

            $j=0;
            foreach ( $lines as $line )
            {
                if ( $j > 0 )
                {
                    $array[] = str_getcsv( $line );
                }

                $j ++;
            }
        }

        $buildingTypes = [
            1 => 1,
            2 => 3,
            3 => 5,
            4 => 4,
            5 => 3,
            6 => 4,
            7 => 1
        ];

        foreach ($array as $item)
        {
            if(count($item) < 12 || empty($item[ 8 ]) || empty($item[ 7 ]) || empty($item[ 10 ]) || empty($item[ 11 ]) || empty($item[ 4 ]) || empty($item[ 0 ])) continue;

            $guidBuilding = \App\Models\Building::where('gzhi_address_guid', $item[ 0 ])->first();

            $nameBuilding = \App\Models\Building::where('name', $item[ 4 ])->first();

            if($guidBuilding || $nameBuilding) continue;

            $building = new \App\Models\Building();

            $building->provider_id = 1;

            $streetSegment = \App\Models\Segment::whereName($item[ 8 ])->first();

            if(!$streetSegment){
                $streetSegment = new \App\Models\Segment();
                $streetSegment->provider_id = 1;
                $streetSegment->segment_type_id = 3;

                $parentSegment = \App\Models\Segment::whereName($item[ 7 ])->first();
                if(!$parentSegment)
                {
                    $parentSegment = new \App\Models\Segment();
                    $parentSegment->provider_id = 1;
                    $parentSegment->segment_type_id = 6;
                    $parentSegment->parent_id = 63;
                    $parentSegment->name = $item[ 7 ];
                    $parentSegment->save();
                }

                $streetSegment->parent_id = $parentSegment->id;
                $streetSegment->name = $item[ 8 ];
                $streetSegment->save();
            }

            $building->segment_id = $streetSegment->id;
            $building->building_type_id = $buildingTypes[$item[ 11 ]];
            $building->name = $item[ 4 ];
            $building->number = $item[ 10 ];
            $building->gzhi_address_guid = $item[ 0 ];
            $building->fais_address_guid = $item[ 1 ];
            $building->save();

            if(count($item) > 12){
                $management = \App\Models\Management::where('guid', $item[ 12 ])->first();
                if($management)
                {
                    \Illuminate\Support\Facades\DB::table('managements_buildings')->insert(
                        ['management_id' => $management->id, 'building_id' => $building->id]
                    );
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
