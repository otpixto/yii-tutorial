<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissedCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missed_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 10)->unique()->notNull();
            $table->string('call_id', 30)->nullable();
            $table->integer('calls_count')->devaultValue(1);
            $table->timestamp('create_date')->notNull();
            $table->timestamp('call_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missed_calls');
    }
}
