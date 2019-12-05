<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGzhiColumsToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('gzhi_appeal_number', 30)->after('mosreg_id')->nullable()->comment('edsAppealNumber интеграции ГЖИ');
            $table->string('gzhi_number_eds', 30)->after('gzhi_appeal_number')->nullable()->comment('edsNumberEDS интеграции ГЖИ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('gzhi_appeal_number');
            $table->dropColumn('gzhi_number_eds');
        });
    }
}
