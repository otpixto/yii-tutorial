<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillDobrodelTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        $fileName = 'files/types_dobrodel.csv';
//
//        $csvData = File::get( storage_path( $fileName ) );
//
//        $lines = explode( PHP_EOL, $csvData );
//
//        $array = array();
//
//        $i = 0;
//
//        foreach ( $lines as $line )
//        {
//            if ( $i > 0 )
//            {
//                $array[] = str_getcsv( $line );
//            }
//
//            $i ++;
//        }
//
//        foreach ( $array as $one )
//        {
//
//            if ( ! isset( $one[ 0 ] ) || ! isset( $one[ 4 ] ) )
//            {
//                continue;
//            }
//
//            $parentId = $one[ 5 ];
//
//            $type = new \App\Models\Type();
//            $type->parent_id = $parentId;
//            $type->name = $one[ 6 ];
//            $type->period_acceptance = $one[ 7 ];
//            $type->period_execution = $one[ 8 ];
//            $type->need_act = (bool)$one[ 9 ];
//            $type->save();
//
//        }
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
