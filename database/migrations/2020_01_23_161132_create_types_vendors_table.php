<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypesVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });

        Schema::create('types_vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type_id');
            $table->tinyInteger('vendor_id');
        });


        $fileName = 'files/types_vendors.csv';

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

            if ( ! isset( $one[ 0 ] ) || $one[ 0 ] == "" || ! isset( $one[ 3 ] ) || $one[ 3 ] == "" )
            {
                continue;
            }

            $typeID = $one[ 0 ];

            $type = \App\Models\Type::find($typeID);

            if(!$type){
                continue;
            }

            if(strpos($one[ 3 ], ';')){
                $typeIDArray = explode(';', $one[ 3 ]);
                \Illuminate\Support\Facades\DB::table('types_vendors')->insert([
                    [
                        'type_id' => $typeID,
                        'vendor_id' => $typeIDArray[0]
                    ],
                    [
                        'type_id' => $typeID,
                        'vendor_id' => $typeIDArray[1]
                    ]
                ]);
            } else {
                $vendorID = (int)$one[ 3 ];

                \Illuminate\Support\Facades\DB::table('types_vendors')->insert([
                    [
                        'type_id' => $typeID,
                        'vendor_id' => $vendorID
                    ]
                ]);
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
        Schema::dropIfExists('types_vendors');
    }
}
