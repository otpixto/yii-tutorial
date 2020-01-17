<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillTypesTableTwo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        $type = new \App\Models\Type();
//        $type->provider_id = 1;
//        $type->parent_id = 1101;
//        $type->name = '99.21.44. МП: Отсутствие информационного и (или) информации об УО на стендах в МОП';
//        $type->period_acceptance = 24;
//        $type->period_execution = 192;
//        $type->mosreg_id = 714;
//        $type->gzhi_name = 'МП: Отсутствие информационного и (или) информации об УО на стендах в МОП';
//        $type->save();
//
//        $fileName = 'files/fill_types_two.csv';
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
//            if ( ! isset( $one[ 0 ] ) || $one[ 0 ] == "" || ! isset( $one[ 4 ] ) )
//            {
//                continue;
//            }
//
//            $id = $one[ 0 ];
//
//            $type = \App\Models\Type::find($id);
//
//            if($type){
//                $type->group_id = $one[ 4 ];
//                $type->name = $one[ 6 ];
//                $type->gzhi_code_type = $one[ 20 ];
//                $type->save();
//            }
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
