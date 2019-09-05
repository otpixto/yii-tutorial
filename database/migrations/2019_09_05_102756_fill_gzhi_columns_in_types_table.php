<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillGzhiColumnsInTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fileName = 'files/types_ours_201909041548_ES.csv';

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

        foreach ($array as $one)
        {

            if(!isset($one[0]) || !isset($one[4]))
            {
                continue;
            }

            $id = $one[0];

            $type = \App\Models\Type::find($id);

            if($type)
            {
                $type->gzhi_code = ($one[3] == '') ? '99001' : $one[3];

                $type->gzhi_code_type = ($one[4] == '') ? '99' : $one[4];

                $type->save();
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
