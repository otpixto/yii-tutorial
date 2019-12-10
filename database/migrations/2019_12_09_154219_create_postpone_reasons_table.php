<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostponeReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('decline_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('eds_number', 10)->nullable()->comment('№ п/п в ЕДС');
            $table->string('type', 100)->nullable()->comment('Вид');
            $table->string('name', 255)->notNull()->comment('Наименование');
            $table->string('eds_code', 10)->nullable()->comment('Код ЕДС МО');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->tinyInteger('decline_reason_id')->after('mosreg_id')->nullable()->comment('ID причины отклонения (decline_reasons)');
        });

        \Illuminate\Support\Facades\DB::table('decline_reasons')->insert([
            [
                'eds_number' => 10,
                'type' => 'Заявка на работы',
                'name' => 'Требования не соответствующие договору управления и Постановлению Госстроя РФ № 170 от 27.09.2003',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 20,
                'type' => 'Заявка на работы',
                'name' => 'Работы относятся к категории капитального ремонта',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 30,
                'type' => 'Заявка на работы',
                'name' => 'Отзыв заявки заявителем',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 50,
                'type' => 'Заявка на работы',
                'name' => 'Организация не оказывает услуг подобного характера',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 70,
                'type' => 'Заявка на работы',
                'name' => 'Решение вопроса не в компетенции организации',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 90,
                'type' => 'Заявка на работы',
                'name' => 'Требования не соответствуют условиям договора управления',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 110,
                'type' => 'Заявка на работы',
                'name' => 'Территория не обслуживается организацией',
                'eds_code' => '7292'
            ],
            [
                'eds_number' => 130,
                'type' => 'Заявка на работы',
                'name' => 'Дом не обслуживается организацией',
                'eds_code' => '7292'
            ],
            [
                'eds_number' => 150,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие необходимых подтверждающих документов',
                'eds_code' => '7145'
            ],
            [
                'eds_number' => 170,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие обратной связи от Заявителя',
                'eds_code' => '7145'
            ],
            [
                'eds_number' => 190,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие доступа для выполнения работ',
                'eds_code' => '7145'
            ],
            [
                'eds_number' => 210,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие своевременной поверки приборов учета',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 230,
                'type' => 'Заявка на работы',
                'name' => 'Отсутствие решения общего собрания собственников по данному вопросу',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 250,
                'type' => 'Заявка на работы',
                'name' => 'Решение контролирующего органа',
                'eds_code' => '7439'
            ],
            [
                'eds_number' => 270,
                'type' => 'Заявка на работы',
                'name' => 'Прочее',
                'eds_code' => '7439'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('decline_reasons');
    }
}
