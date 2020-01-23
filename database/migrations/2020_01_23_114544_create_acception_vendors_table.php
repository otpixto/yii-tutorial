<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceptionVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_selectable')->after('name')->setDefault(0);
        });

        \Illuminate\Support\Facades\DB::table('vendors')->insert([
            [
                'name' => 'ЕДС-регион',
                'is_selectable' => 1
            ],
            [
                'name' => 'ЕЦУР',
                'is_selectable' => 1
            ]
        ]);

        $vendor1 = \App\Models\Vendor::find(2);
        $vendor1->is_selectable = true;
        $vendor1->save();


        $vendor2 = \App\Models\Vendor::find(3);
        $vendor2->is_selectable = true;
        $vendor2->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('vendors');
    }
}
