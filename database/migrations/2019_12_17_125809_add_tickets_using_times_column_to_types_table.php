<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTicketsUsingTimesColumnToTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('types', function(Blueprint $table) {
            $table->smallInteger('tickets_using_times')->nullable()->after('gzhi_name');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->text('favorite_types_list')->nullable()->after('tabs_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('types', function(Blueprint $table) {
            $table->dropColumn('tickets_using_times');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('favorite_types_list');
        });
    }
}
