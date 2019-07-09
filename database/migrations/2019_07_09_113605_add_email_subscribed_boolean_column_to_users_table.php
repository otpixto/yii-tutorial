<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailSubscribedBooleanColumnToUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\App\User::$_table, function (Blueprint $table) {
           $table->boolean('email_subscribed')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\App\User::$_table, function (Blueprint $table) {
            $table->dropColumn('email_subscribed');
        });
    }
}
