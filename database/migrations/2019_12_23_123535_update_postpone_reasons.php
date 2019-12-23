<?php

use Illuminate\Database\Migrations\Migration;

class UpdatePostponeReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rejectReason = \App\Models\RejectReason::where('eds_number', '270')->first();
        if($rejectReason)
        {
            $rejectReason->eds_code = '7429';
            $rejectReason->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
