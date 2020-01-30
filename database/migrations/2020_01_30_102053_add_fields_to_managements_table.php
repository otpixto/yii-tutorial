<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('managements', function (Blueprint $table) {
            $table->string('legal_address', 150)->after('services')->nullable()->comment('Юридический адрес организации');
            $table->string('inn', 30)->after('legal_address')->nullable()->comment('ИНН организации');
            $table->string('kpp', 30)->after('inn')->nullable()->comment('КПП организации');
            $table->string('ogrn', 30)->after('kpp')->nullable()->comment('ОГРН организации');
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
            $table->dropColumn('legal_address');
            $table->dropColumn('inn', 30)->after('legal_address')->nullable()->comment('ИНН организации');
            $table->dropColumn('kpp', 30)->after('inn')->nullable()->comment('КПП организации');
            $table->dropColumn('ogrn', 30)->after('kpp')->nullable()->comment('ОГРН организации');
        });
    }
}
