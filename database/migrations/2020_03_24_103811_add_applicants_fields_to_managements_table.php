<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicantsFieldsToManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('managements', function (Blueprint $table) {
            $table->string('applicants_lastname', 50)->after('gzhi_guid')->nullable();
            $table->string('applicants_name', 50)->after('applicants_lastname')->nullable();
            $table->string('applicants_middlename', 50)->after('applicants_name')->nullable();
            $table->string('applicants_phone', 30)->after('applicants_middlename')->nullable();
            $table->string('applicants_extra_phone', 30)->after('applicants_phone')->nullable();
            $table->string('applicants_email', 50)->after('applicants_extra_phone')->nullable();
            $table->integer('applicants_building_id')->after('applicants_email')->nullable();
            $table->string('applicants_actual_flat', 10)->after('applicants_building_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('managements', function (Blueprint $table) {
            $table->dropColumn('applicants_lastname');
            $table->dropColumn('applicants_name');
            $table->dropColumn('applicants_middlename');
            $table->dropColumn('applicants_phone');
            $table->dropColumn('applicants_extra_phone');
            $table->dropColumn('applicants_email');
            $table->dropColumn('applicants_building_id');
            $table->dropColumn('applicants_actual_flat');
        });
    }
}
