<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillGzhiColumnInStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('gzhi_status_name', 150)->after('status_name')->nullable()->comment('Статус интеграции ГЖИ');
            $table->string('gzhi_status_code', 50)->after('status_code')->nullable()->comment('Код статуса интеграции ГЖИ');
        });


        $fileName = 'files/statuses_ours_201909061034_ES.csv';

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

            $type = \App\Models\Status::find($id);

            if($type)
            {
                $type->gzhi_status_name = $one[3];

                $type->gzhi_status_code = $one[4];

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
