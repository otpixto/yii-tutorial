<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldsInProviderContextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $providerContext = \App\Models\ProviderContext::find(2);
        $providerContext->name = 'Исходящие с набором номера';
        $providerContext->save();

        $providerContext2 = \App\Models\ProviderContext::find(3);
        $providerContext2->name = 'Исходящие без набора номера';
        $providerContext2->save();
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
