<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillDobrodelTypesEds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $fileName = 'files/fill_types_three.csv';

        $csvData = File::get( storage_path( $fileName ) );

        $lines = explode( PHP_EOL, $csvData );

        $array = array();

        $i = 0;

        foreach ( $lines as $line )
        {
            if ( $i > 0 )
            {
                $array[] = str_getcsv( $line, ';' );
            }

            $i ++;
        }

        $parentId = null;
        foreach ( $array as $one )
        {

            if(!isset($one[ 2 ])) continue;

            if(empty($one[ 2 ]))
            {
                $parentId = null;
            }

            $name = mb_strimwidth(str_replace('"', '', trim($one[ 3 ])), 0, 191);

            $typeName = \App\Models\Type::whereName( $name )->where('provider_id', 1)->first();

            if($typeName){
                $typeName->forceDelete();
            }

            $typeName = \App\Models\Type::whereName( $name )->whereNull('provider_id')->first();

            if($typeName){
                $typeName->forceDelete();
            }

            $type = new \App\Models\Type();
            $type->parent_id = $parentId;
            $type->provider_id = 1;
            $type->name = $name;
            $type->period_acceptance = $one[ 4 ];
            $type->period_execution = $one[ 5 ];
            $type->need_act = $one[ 6 ];
            $type->save();

            if(empty($one[ 2 ]))
            {
                $parentId = $type->id;
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
