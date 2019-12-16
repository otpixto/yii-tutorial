<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFaisAddressGuidToBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buildings', function(Blueprint $table) {
            $table->string('fais_address_guid', 50)->nullable()->after('gzhi_address_guid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buildings', function(Blueprint $table) {
            $table->dropColumn('fais_address_guid');
        });
    }
}
