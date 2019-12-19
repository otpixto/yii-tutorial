<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillPeriosExecutionColumnInTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fileName = 'files/types_deadline.csv';

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

        foreach ( $array as $one )
        {

            if ( ! isset( $one[ 0 ] ) || ! isset( $one[ 4 ] ) )
            {
                continue;
            }

            $mosregId = $one[ 0 ];

            $type = \App\Models\Type::where('mosreg_id', $mosregId)->first();

            if ($type){

                $type->period_execution = (int)$one[ 6 ];

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
