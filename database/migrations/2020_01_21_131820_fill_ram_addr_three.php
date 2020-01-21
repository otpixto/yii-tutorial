<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillRamAddrThree extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {

        $array = array();

        $fileName = 'files/ram_addr_for_upload0.csv';

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
            if(!isset($item[ 11 ]) || empty($item[ 11 ])) continue;

            if ( count( $item ) > 10 || $item[ 7 ] == '' || $item[ 8 ] == '' || $item[ 10 ] == '' )
            {

                $fullAddress = (string) $item[ 4 ];

                $fullAddressArray = explode(',', $fullAddress);

                if(count($fullAddressArray) < 3) continue;

                $city = $item[7];

                if( $item[7] == '' && count($fullAddressArray) == 3 )
                {
                    if(strpos($fullAddressArray[2], ' д.') !== false || strpos($fullAddressArray[2], ' стр.') !== false ){
                        $city = $fullAddressArray[1];
                    } else {
                        continue;
                    }
                }

                if( $item[7] == '' && count($fullAddressArray) == 4 )
                {
                    $city = $fullAddressArray[1];
                }

                if( $item[7] == '' && count($fullAddressArray) == 5 )
                {
                    if(strpos($fullAddressArray[4], ' д.') !== false  || strpos($fullAddressArray[4], ' стр.') !== false ){
                        $city = $fullAddressArray[1] . ', ' . $fullAddressArray[2];
                    } else {
                        continue;
                    }
                }

                $street = $item[8];

                if( $item[8] == '' && count($fullAddressArray) == 4 )
                {
                    if(strpos($fullAddressArray[3], ' д.') !== false  || strpos($fullAddressArray[3], ' стр.') !== false ){
                        $street = $fullAddressArray[2];
                    } else {
                        continue;
                    }
                }


                if( $item[8] == '' && count($fullAddressArray) == 5 )
                {
                    if(strpos($fullAddressArray[4], ' д.') !== false || strpos($fullAddressArray[4], ' стр.') !== false ){
                        $street = $fullAddressArray[3];
                    } else {
                        continue;
                    }
                }


                $houseNumber = $item[10];

                if( $item[10] == '' && count($fullAddressArray) == 3 )
                {
                    continue;
                }

                if( $item[10] == '' && count($fullAddressArray) == 4 )
                {
                    if(strpos($fullAddressArray[3], ' д.') !== false || strpos($fullAddressArray[3], ' стр.') !== false ){
                        $houseNumber = $fullAddressArray[3];
                    } else {
                        continue;
                    }
                }

                if( $item[10] == '' && count($fullAddressArray) == 5 )
                {
                    if(strpos($fullAddressArray[4], ' д.') !== false || strpos($fullAddressArray[4], ' стр.') !== false ){
                        $houseNumber = $fullAddressArray[4];
                    } else {
                        continue;
                    }
                }

                $houseNumber = trim(str_replace('д.', '', $houseNumber));

                $city = trim($city);

                $street = trim($street);

                if($city == '' || $houseNumber == "" || $street == "") continue;

                $guidBuilding = \App\Models\Building::where( 'gzhi_address_guid', $item[ 0 ] )
                    ->first();

                $nameBuilding = \App\Models\Building::where( 'name', $item[ 4 ] )
                    ->first();

                if ( $guidBuilding || $nameBuilding ) continue;

                $building = new \App\Models\Building();

                $building->provider_id = 1;

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
                $building->building_type_id = $buildingTypes[ $item[ 11 ] ];
                $building->name = $item[ 4 ];
                $building->number = $houseNumber;
                $building->gzhi_address_guid = $item[ 0 ];
                $building->fais_address_guid = $item[ 1 ];
                $building->save();

                if ( count( $item ) > 12 )
                {
                    $management = \App\Models\Management::where( 'guid', $item[ 12 ] )
                        ->first();
                    if ( $management )
                    {
                        \Illuminate\Support\Facades\DB::table( 'managements_buildings' )
                            ->insert(
                                [ 'management_id' => $management->id, 'building_id' => $building->id ]
                            );
                    }
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
