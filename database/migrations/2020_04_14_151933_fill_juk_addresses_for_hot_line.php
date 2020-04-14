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
    public function up ()
    {

//        $array = array();
//
//        $fileName = 'files/ram_addr_for_upload0.csv';
//
//        $csvData = \Illuminate\Support\Facades\File::get( storage_path( $fileName ) );
//
//        $lines = explode( PHP_EOL, $csvData );
//
//        $j = 0;
//        foreach ( $lines as $line )
//        {
//            if ( $j > 0 )
//            {
//                $d = str_getcsv( $line );
//                $array[ $d[ 0 ] ] = str_getcsv( $line );
//            }
//
//            $j ++;
//        }
//
//
////        $dataArray = [];
////        $i = 0;
////        foreach ( $array as $one )
////        {
////            if ( count( $one ) > 1 || $one[ 7 ] == '' || $one[ 8 ] == '' || $one[ 10 ] == '' )
////            {
////
////
////
////                $dataArray[ $i ][ 'edsAddressGUID' ] = (string) $one[ 0 ] ?? '';
////
////                $dataArray[ $i ][ 'edsFIASAddressGUID' ] = (string) $one[ 1 ] ?? '';
////
////                $dataArray[ $i ][ 'edsAddressType' ] = (string) $one[ 2 ] ?? '';
////
////                $dataArray[ $i ][ 'edsOKTMO' ] = (string) $one[ 3 ] ?? '';
////
////                $dataArray[ $i ][ 'edsAddressName' ] = (string) $one[ 4 ] ?? '';
////
////                $dataArray[ $i ][ 'edsRegion' ] = (string) $one[ 5 ] ?? '';
////
////                $dataArray[ $i ][ 'edsArea' ] = (string) $one[ 6 ] ?? '';
////
////                $dataArray[ $i ][ 'edsCity' ] = (string) $one[ 7 ] ?? '';
////
////                $dataArray[ $i ][ 'edsStreet' ] = (string) $one[ 8 ] ?? '';
////
////                $dataArray[ $i ][ 'edsEstStatus' ] = (string) $one[ 9 ] ?? '';
////
////                $dataArray[ $i ][ 'edsHouseNum' ] = (string) $one[ 10 ] ?? '';
////
////                $dataArray[ $i ][ 'edsHouseType' ] = (string) $one[ 11 ] ?? '';
////
////
////                if ( count( $one ) > 12 )
////                {
////
////                    $dataArray[ $i ][ 'edsOrgGUID' ] = (string) $one[ 12 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsName' ] = (string) $one[ 13 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsFullName' ] = (string) $one[ 14 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsAddress' ] = (string) $one[ 15 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsAddressJur' ] = (string) $one[ 16 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsAddressDisp' ] = (string) $one[ 17 ] ?? '';
////
////                    $dataArray[ $i ][ 'edsTypeOrg' ] = (string) $one[ 18 ] ?? '';
////
////                }
////
////                $i ++;
////            }
////        }
////
////        $headersArray = array( 'edsAddressGUID', 'edsFIASAddressGUID', 'edsAddressType', 'edsOKTMO', 'edsAddressName', 'edsRegion'
////        , 'edsArea'
////        , 'edsCity'
////        , 'edsStreet'
////        , 'edsEstStatus'
////        , 'edsHouseNum'
////        , 'edsHouseType',
////            'edsOrgGUID',
////            'edsName',
////            'edsFullName',
////            'edsAddress',
////            'edsAddressJur',
////            'edsAddressDisp',
////            'edsTypeOrg'
////        );
////
////        $this->generateCSV( $headersArray, $dataArray, 0 );
////
//        $buildingTypes = [
//            1 => 1,
//            2 => 3,
//            3 => 5,
//            4 => 4,
//            5 => 3,
//            6 => 4,
//            7 => 1
//        ];
//
//        $k =0;
//
//        foreach ($array as $item)
//        {
//            if(!isset($item[ 10 ])) continue;
//
//            if ( count( $item ) > 10 || $item[ 7 ] == '' || $item[ 8 ] == '' || $item[ 10 ] == '' )
//            {
//
//                $fullAddress = (string) $item[ 4 ];
//
//                $fullAddressArray = explode(',', $fullAddress);
//
//                if(count($fullAddressArray) < 3) continue;
//
//                $city = $item[7];
//
//                if( $item[7] == '' && count($fullAddressArray) == 3 )
//                {
//                    if(strpos($fullAddressArray[2], ' д.') !== false || strpos($fullAddressArray[2], ' стр.') !== false ){
//                        $city = $fullAddressArray[1];
//                    } else {
//                        continue;
//                    }
//                }
//
//                if( $item[7] == '' && count($fullAddressArray) == 4 )
//                {
//                    $city = $fullAddressArray[1];
//                }
//
//                if( $item[7] == '' && count($fullAddressArray) == 5 )
//                {
//                    if(strpos($fullAddressArray[4], ' д.') !== false  || strpos($fullAddressArray[4], ' стр.') !== false ){
//                        $city = $fullAddressArray[1] . ', ' . $fullAddressArray[2];
//                    } else {
//                        continue;
//                    }
//                }
//
//                $street = $item[8];
//
//                if( $item[8] == '' && count($fullAddressArray) == 4 )
//                {
//                    if(strpos($fullAddressArray[3], ' д.') !== false  || strpos($fullAddressArray[3], ' стр.') !== false ){
//                        $street = $fullAddressArray[2];
//                    } else {
//                        continue;
//                    }
//                }
//
//
//                if( $item[8] == '' && count($fullAddressArray) == 5 )
//                {
//                    if(strpos($fullAddressArray[4], ' д.') !== false || strpos($fullAddressArray[4], ' стр.') !== false ){
//                        $street = $fullAddressArray[3];
//                    } else {
//                        continue;
//                    }
//                }
//
//
//                $houseNumber = $item[10];
//
//                if( $item[10] == '' && count($fullAddressArray) == 3 )
//                {
//                    continue;
//                }
//
//                if( $item[10] == '' && count($fullAddressArray) == 4 )
//                {
//                    if(strpos($fullAddressArray[3], ' д.') !== false || strpos($fullAddressArray[3], ' стр.') !== false ){
//                        $houseNumber = $fullAddressArray[3];
//                    } else {
//                        continue;
//                    }
//                }
//
//                if( $item[10] == '' && count($fullAddressArray) == 5 )
//                {
//                    if(strpos($fullAddressArray[4], ' д.') !== false || strpos($fullAddressArray[4], ' стр.') !== false ){
//                        $houseNumber = $fullAddressArray[4];
//                    } else {
//                        continue;
//                    }
//                }
//
//                $houseNumber = trim(str_replace('д.', '', $houseNumber));
//
//                $city = trim($city);
//
//                $street = trim($street);
//
//                if($city == '' || $houseNumber == "") continue;
//
//                $guidBuilding = \App\Models\Building::where( 'gzhi_address_guid', $item[ 0 ] )
//                    ->first();
//
//                $nameBuilding = \App\Models\Building::where( 'name', $item[ 4 ] )
//                    ->first();
//
//                if ( $guidBuilding || $nameBuilding ) continue;
//
//                dd($guidBuilding);
////
////                $building = new \App\Models\Building();
////
////                $building->provider_id = 1;
////
////                $streetSegment = \App\Models\Segment::whereName( $street )
////                    ->first();
////
////                if ( ! $streetSegment )
////                {
////                    $streetSegment = new \App\Models\Segment();
////                    $streetSegment->provider_id = 1;
////                    $streetSegment->segment_type_id = 3;
////
////                    $parentSegment = \App\Models\Segment::whereName( $city )
////                        ->first();
////                    if ( ! $parentSegment )
////                    {
////                        $parentSegment = new \App\Models\Segment();
////                        $parentSegment->provider_id = 1;
////                        $parentSegment->segment_type_id = 6;
////                        $parentSegment->parent_id = 63;
////                        $parentSegment->name = $city;
////                        $parentSegment->save();
////                    }
////
////                    $streetSegment->parent_id = $parentSegment->id;
////                    $streetSegment->name = $street;
////                    $streetSegment->save();
////                }
////
////                $building->segment_id = $streetSegment->id;
////                $building->building_type_id = $buildingTypes[ $item[ 11 ] ];
////                $building->name = $item[ 4 ];
////                $building->number = $houseNumber;
////                $building->gzhi_address_guid = $item[ 0 ];
////                $building->fais_address_guid = $item[ 1 ];
////                $building->save();
////
////                if ( count( $item ) > 12 )
////                {
////                    $management = \App\Models\Management::where( 'guid', $item[ 12 ] )
////                        ->first();
////                    if ( $management )
////                    {
////                        \Illuminate\Support\Facades\DB::table( 'managements_buildings' )
////                            ->insert(
////                                [ 'management_id' => $management->id, 'building_id' => $building->id ]
////                            );
////                    }
////                }
//            }
//            $k++;
//        }
//
//        dd($k);

    }

    private function generateCSV ( array $headersArray, array $dataArray, $index = 1 )
    {
        $filename = 'ram_addr_for_upload' . $index . '.csv';

        $output = fopen( storage_path() . '/files/' . $filename, 'w' );

        fputcsv( $output, $headersArray );

        foreach ( $dataArray as $row )
        {
            fputcsv( $output, $row );
        }
        fclose( $output );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down ()
    {
        //
    }
}
