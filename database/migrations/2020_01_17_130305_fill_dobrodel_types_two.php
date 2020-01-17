<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillDobrodelTypesTwo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
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
                $array[] = str_getcsv( $line );
            }

            $i ++;
        }


        $parentId = null;
        foreach ( $array as $one )
        {

            if ( ! isset( $one[ 0 ] ) || ! isset( $one[ 4 ] ) )
            {
                continue;
            }

            $typeName = \App\Models\Type::whereName( $one[ 4 ] )->first();

            if($typeName) continue;

            $id = $one[ 0 ];

            $type = \App\Models\Type::find( $id );

            if ( ! $type )
            {
                $type = new \App\Models\Type();
            }

            $type->provider_id = 1;
            if ( $one[ 2 ] > 0 )
            {
                $type->parent_id = $one[ 2 ];
            }

            $type->name = $one[ 3 ];
            $type->period_acceptance = $one[ 4 ];
            $type->period_execution = $one[ 5 ];
            $type->need_act = $one[ 6 ];
            $type->save();


            $type2 = new  \App\Models\Type();

            $type2->provider_id = 9;
            if ( $one[ 2 ] > 0 && $parentId )
            {
                $type2->parent_id = $parentId;
            }

            $type2->name = $one[ 3 ];
            $type2->period_acceptance = $one[ 4 ];
            $type2->period_execution = $one[ 5 ];
            $type2->need_act = $one[ 6 ];
            $type2->save();

            if(empty($one[ 2 ]))
            {
                $parentId = $type2->id;
            }

        }
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
