<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColorColumnFromCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function(Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('tickets', function(Blueprint $table) {
            $table->dropColumn('decline_reason_id');
            $table->tinyInteger('reject_reason_id')->nullable()->after('mosreg_id');
            $table->string('reject_comment', 512)->nullable()->after('reject_reason_id');
        });
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
