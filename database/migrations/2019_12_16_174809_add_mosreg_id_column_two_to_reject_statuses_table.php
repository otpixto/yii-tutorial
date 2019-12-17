<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMosregIdColumnTwoToRejectStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i=16; $i<23; $i++)
        {
            $rejectReason = \App\Models\RejectReason::find($i);
            if($rejectReason)
            {
                $rejectReason->delete();
            }
        }

        $fileName = 'files/reject_reasons_data.csv';

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

            if ( ! isset( $one[ 0 ] ) || ! isset( $one[ 4 ] ) || ! isset( $one[ 5 ] ) )
            {
                continue;
            }

            $id = $one[ 0 ];

            $type = \App\Models\RejectReason::find($id);

            $type->mosreg_id = $one[ 5 ];

            $type->save();

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
