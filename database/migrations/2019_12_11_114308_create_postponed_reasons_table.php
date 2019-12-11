<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostponedReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postpone_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('eds_number', 10)->nullable()->comment('№ п/п в ЕДС');
            $table->string('type', 100)->nullable()->comment('Вид');
            $table->string('name', 255)->notNull()->comment('Наименование');
            $table->string('eds_code', 10)->nullable()->comment('Код ЕДС МО');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        \Illuminate\Support\Facades\DB::table('postpone_reasons')->insert([
            [
                'eds_number' => 10,
                'type' => 'Заявка на работы',
                'name' => 'Сезонные работы'
            ],
            [
                'eds_number' => 20,
                'type' => 'Заявка на работы',
                'name' => 'Нехватка материалов'
            ],
            [
                'eds_number' => 30,
                'type' => 'Заявка на работы',
                'name' => 'Нехватка специалистов'
            ],
            [
                'eds_number' => 40,
                'type' => 'Заявка на работы',
                'name' => 'Вопрос, относящийся к деятельности РСО'
            ],
            [
                'eds_number' => 50,
                'type' => 'Заявка на работы',
                'name' => 'Решение контролирующего органа'
            ],
            [
                'eds_number' => 70,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие финансирования'
            ],
            [
                'eds_number' => 90,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие запрашиваемой информации'
            ],
            [
                'eds_number' => 110,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие ресурсов для выполнения работ'
            ],
            [
                'eds_number' => 130,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие обратной связи от Заявителя'
            ],
            [
                'eds_number' => 150,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие доступа для выполнения работ'
            ],
            [
                'eds_number' => 170,
                'type' => 'Заявка на работы',
                'name' => 'Аварийная ситуация'
            ],
            [
                'eds_number' => 190,
                'type' => 'Заявка на работы',
                'name' => 'Сбой электронной техники/ программного обеспечения'
            ],
            [
                'eds_number' => 210,
                'type' => 'Заявка на работы',
                'name' => 'Работы невозможно выполнить в данное время года (Сезонные работы)'
            ],
            [
                'eds_number' => 230,
                'type' => 'Заявка на работы',
                'name' => 'Прочее'
            ],
        ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->tinyInteger('postpone_reason_id')->after('postponed_comment')->nullable()->comment('ID причины отложки (postpone_reasons)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postponed_reasons');
    }
}
