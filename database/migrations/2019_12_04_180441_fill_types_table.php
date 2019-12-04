<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {
        $fileName = 'files/fill_types.csv';

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

            $providerId = $one[ 0 ];

            $type = new \App\Models\Type();

            $type->provider_id = $providerId;

            $type->parent_id = $one[ 1 ];

            $type->name = $one[ 2 ];

            $type->period_acceptance = $one[ 4 ];

            $type->period_execution = $one[ 5 ];

            $type->season = $one[ 6 ];

            $type->emergency = ($one[ 7 ] == 'Да') ? 1 : 0;

            $type->save();

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
