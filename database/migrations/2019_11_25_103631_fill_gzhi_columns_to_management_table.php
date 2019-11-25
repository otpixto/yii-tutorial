<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillGzhiColumnsToManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {

        $fileName = 'files/managements_201911221053_ES.csv';

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
            if(!isset($one[0]) || !isset($one[3]) || $one[3] == '')
            {
                continue;
            }
            $id = $one[0];

            $management = \App\Models\Management::find($id);

            if($management)
            {
                $management->gzhi_guid = $one[3];

                if($one[1] != '')
                {
                    $management->guid = $one[1];
                }

                $management->save();
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
