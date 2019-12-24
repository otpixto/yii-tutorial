<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $types = \App\Models\Type::whereIn('parent_id', [399, 400, 401, 402, 403, 404, 405, 406, 407, 409, 410, 412])->get();
        foreach ($types as $type)
        {
            $type->need_act = 0;
            $type->save();
        }
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
