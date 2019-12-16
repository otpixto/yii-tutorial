<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMosregIdColumnToRejectStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reject_reasons', function(Blueprint $table) {
            $table->string('mosreg_id', 10)->nullable()->after('eds_code');
        });

        $array = [
            4635 => 'Решено. Выявлены нарушения, меры приняты',
            4782 => 'Факты не подтвердились. Повреждений не выявлено',
            4929 => 'Отклонено. Ответ по проблеме предоставлялся ранее',
            5076 => 'Отложено. Ожидается поставка материала',
            5223 => 'Запрос информации. Недостаточно информации для решения проблемы',
            5370 => 'Отклонено. Объект не находится в обслуживании организации',
            5517 => 'Отклонено. Вопрос не в компетенции организации',
        ];

        foreach ($array as $key => $value)
        {

            $rejectReason = new \App\Models\RejectReason();
            $rejectReason->mosreg_id = $key;
            $rejectReason->name = $value;
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
        Schema::table('reject_reasons', function(Blueprint $table) {
            $table->dropColumn('mosreg_id');
        });
    }
}
