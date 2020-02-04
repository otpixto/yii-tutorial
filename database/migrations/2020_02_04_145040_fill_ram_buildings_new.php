<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillRamBuildingsNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {

        $array = array();

        $fileName = 'files/ram_buildings.csv';

        $csvData = \Illuminate\Support\Facades\File::get( storage_path( $fileName ) );

        $lines = explode( PHP_EOL, $csvData );

        $j = 0;
        foreach ( $lines as $line )
        {
            if ( $j > 0 )
            {
                $d = str_getcsv( $line );
                $array[ $d[ 0 ] ] = str_getcsv( $line );
            }

            $j ++;
        }

        $i = 0;
        foreach ($array as $item)
        {
            if(!isset($item[ 0 ]) || empty($item[ 1 ])) continue;

            if ( $item[ 0 ] != '' && $item[ 1 ] != '')
            {

                $buildingID = (int)$item[ 0 ];
                $building = \App\Models\Building::find($buildingID);

                if(!$building) continue;

                if($item[ 2 ] == '1')
                {
                    $building->delete();
                    continue;
                }

                $fullAddress = (string) $item[ 1 ];

                $building->name = trim($fullAddress);

                $fullSegmentName = (string) $item[ 4 ];

                $fullAddressArray = explode(',', $fullSegmentName);

                if(count($fullAddressArray) < 2) continue;

                if(count($fullAddressArray) == 3)
                {
                    $street = $fullAddressArray[2];

                    $city = $fullAddressArray[1];

                    $street = trim($street);

                    $city = trim($city);

                    if($street == "") continue;

                    $streetSegment = \App\Models\Segment::whereName( $street )
                        ->first();

                    if ( ! $streetSegment )
                    {
                        $streetSegment = new \App\Models\Segment();
                        $streetSegment->provider_id = 1;
                        $streetSegment->segment_type_id = 3;

                        $parentSegment = \App\Models\Segment::whereName( $city )
                            ->first();
                        if ( ! $parentSegment )
                        {
                            $parentSegment = new \App\Models\Segment();
                            $parentSegment->provider_id = 1;
                            $parentSegment->segment_type_id = 6;
                            $parentSegment->parent_id = 63;
                            $parentSegment->name = $city;
                            $parentSegment->save();
                        }

                        $streetSegment->parent_id = $parentSegment->id;
                        $streetSegment->name = $street;
                        $streetSegment->save();
                    }

                    $building->segment_id = $streetSegment->id;

                } else {

                    $city = $fullAddressArray[1];

                    $city = trim($city);

                    if($city == "") continue;

                    $citySegment = \App\Models\Segment::whereName( $city )
                        ->first();

                    if ( ! $citySegment )
                    {
                        $citySegment = new \App\Models\Segment();
                        $citySegment->provider_id = 1;
                        $citySegment->segment_type_id = 6;
                        $citySegment->parent_id = 63;
                        $citySegment->save();
                    }

                    $building->segment_id = $citySegment->id;
                }

                $building->save();
                $i++;
            }
        }

        echo $i;
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
