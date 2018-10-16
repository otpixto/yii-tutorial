<?php

namespace App\Console\Commands;

use App\Models\Management;
use App\Models\Type;
use Illuminate\Console\Command;

class Mosreg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:mosreg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mosreg';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle ()
    {

        /*$types = [
            1 => '1.1 Прорыв трубы',
            2 => '1.2 Течь вводного крана',
            3 => '1.3 Течь в подвал',
            4 => '1.4 Нет холодной воды',
            5 => '1.5 Течь смесителя в ванной',
            6 => '1.6 Течь смесителя на кухне',
            7 => '1.7 Срыв гибкой подводки в ванной',
            8 => '1.8 Срыв гибкой подводки на кухне',
            9 => '1.9 Срыв гибкой подводки в туалете',
            10 => '1.10 Течь бачка унитаза',
            11 => '1.11 Плохое качество воды',
            12 => '1.12 Нет напора воды на газовую колонку',
            13 => '1.13 Смена пробко-сальникового крана ХВС в подвале',
            14 => '1.14 Нет напора воды в кране ХВС',
            15 => '1.15 Течь под ванной',
            16 => '1.16 Течь с потолка',
            17 => '1.17 Подмес горячей воды в холодную',
            18 => '1.18 Гул в трубах',
            19 => '2.1 Неисправный лифт',
            20 => '2.2 Вибрация кабины лифта при движении',
            21 => '2.3 Переподъем или переспуск кабины относительно этажа',
            22 => '2.4 Неисправное освещение в лифте',
            23 => '2.5 Не работают кнопки в блоке управления',
            24 => '2.6 Кабина лифта не убрана',
            25 => '2.7 Заклинило двери лифта',
            26 => '2.8 Застревание пассажира',
            27 => '2.9 Залитие шахты',
            28 => '2.10 Связь Лифт',
            29 => '3.1 Прорыв газового трубопровода',
            30 => '3.2 Прекращение подачи газа',
            31 => '3.3 Запах газа в подъезде или у подъезда',
            32 => '3.4 Вода в газовой трубе',
            33 => '3.5 Повреждение крепления газопровода',
            34 => '3.6 Запах газа в квартире или у квартиры',
            35 => '3.7 Ремонт газовой плиты',
            36 => '3.8 Ремонт газового котла',
            37 => '3.9 Ремонт газовой колонки',
            38 => '3.10 Замена газовой плиты без сварочных работ',
            39 => '3.11 Замена газовой плиты со сварочными работами',
            40 => '3.12 Демонтаж газовой плиты с установкой заглушки',
            41 => '3.13 Прочистка, калибровка сопла горелки плиты',
            42 => '3.14 Чистка горелки духового шкафа',
            43 => '3.15 Подключение газовой колонки к газопроводу без доп.работ (гофра)',
            44 => '3.16 Замена газовой колонки без изменения подводки с пуском газа и устройством дымоудаления (гофра)',
            45 => '3.17 Замена газовой колонки с новой подводкой водопровода, газопровода, пуском газа и устройством дымоудаления (гофра)',
            46 => '3.18 Снятие, установка теплообменника колонки',
            47 => '3.19 Чистка горелки колонки',
            48 => '3.20 Устранение течи воды в резьбовом соединении',
            49 => '3.21 Замена газового котла без сварочных работ',
            50 => '3.22 Замена газового крана на опуске',
            51 => '3.23 Диагностика и обследование газового оборудование',
            52 => '3.24 Техническое обслуживание ВКГО',
            53 => '4.1 Неисправность входной двери',
            54 => '4.1.1 Неисправность дверей переходных лоджий, лестниц',
            55 => '4.2 Замена/ремонт пружины или доводчика двери подъезда',
            56 => '4.3 Не работает предъподъездное освещение',
            57 => '4.4 Неисправное освещение в подъезде',
            58 => '4.5 Отсутствует остекление в подъезде/разбитые окна (летний период)',
            59 => '4.6 Отсутствует остекление в подъезде/разбитые окна (зимний период)',
            60 => '4.7 Неисправный мусоропровод',
            61 => '4.8 Отсутствуют замки на входных дверях в подвал и/или мусорокамеры',
            62 => '4.9 Неисправность/недоступность инфраструктуры для маломобильных граждан (установка или ремонт пандусов) ',
            63 => '4.10 Неисправность подъемной платформы для инвалидов в подъезде',
            64 => '4.11 Ремонт/замена домофона',
            65 => '4.11.1 Внутриквартирное умстройство',
            66 => '4.11.2 Магнит запирающего устройства',
            67 => '4.11.3 Магнитные ключи (домофон)',
            68 => '4.11.4 Общедомовое устройство',
            69 => '4.11.5 Переговорное устройство',
            70 => '4.11.6 Отсутствует/не работает ЗУ',
            71 => '5.1 Осыпается потолок в подъезде',
            72 => '5.2 Ремонт или замена почтовых ящиков в подъезде',
            73 => '5.3 Ремонт подъезда',
            74 => '5.4 Ремонт перил, поручней',
            75 => '5.5 Ремонт ступеней',
            76 => '5.6 Ремонт козырька над подъездом',
            77 => '5.7 Некачественный текущий ремонт ',
            78 => '6.1 Замена смесителя с душем',
            79 => '6.2 Замена смесителя "Ёлочка"',
            80 => '6.3 Замена полотенцесушителя',
            81 => '6.4 Замена полотенцесушителя (готового хромированного)',
            82 => '6.5 Замена унитаза "Компакт"',
            83 => '6.6 Смена вентиля',
            84 => '6.7 Смена радиатора',
            85 => '6.8 Замена ванной (любой модели)',
            86 => '6.9 Смена умывальника со смесителем',
            87 => '6.10 Смена умывальника без смесителя',
            88 => '6.11 Смена мойки на кронштейнах на 1 отделение',
            89 => '6.12 Установка стиральной машины с подключением к системе водоснабжения',
            90 => '6.13 Смена трубопроводов холодного водоснабжения на трубы из металлопласта',
            91 => '6.14 Смена трубопроводов холодного водоснабжения из стальных труб',
            92 => '6.15 Смена трубопроводов отопления стальных труб на полипропиленовые трубы',
            93 => '6.16 Смена трубопроводов отопления стальных труб на электросварные трубы',
            94 => '6.17 Отключение и включение стояков водоснабжения',
            95 => '6.18 Установка счетчиков холодной или горячей воды с фильтром',
            96 => '6.19 Смена отдельных участков внутренних чугунных канализационных выпусков. Диаметр канализационного выпсука до 50 мм',
            97 => '6.20 Смена отдельных участков внутренних чугунных канализационных выпусков. Диаметр канализационного выпсука до 76-100 мм',
            98 => '6.21 Смена сифона на пластмассовых трубопроводах',
            99 => '6.22 Смена сифона на чугунных трубопроводах',
            100 => '6.23 Устранение течи из гибких подводок присоединения санитарных приборов',
            101 => '6.24 Ремонт смывных бачков типа "Компакт"',
            102 => '6.25 Устранение засора внутренней канализации в трубопроводах',
            103 => '6.26 Устранение засора внутренней канализации в санитарных приборах',
            104 => '6.27 Установка электрического звонка',
            105 => '6.28 Смена выключателя, переключателя, штепсельной розетки скрытой проводки',
            106 => '6.29 Замена скрытой электропроводки отдельных участков',
            107 => '6.30 Смена электроплиты с заменой кабеля до розетки',
            108 => '6.31 Подключение дополнительных электроприборов повышенной мощности',
            109 => '6.32 Разработка технических условий',
            110 => '6.33 Проведение электромонтажных работ',
            111 => '6.34 Замер сопротивления изоляции',
            112 => '6.35 Проверка выполнения ТУ и выдача справки на выполнение работ',
            113 => '6.36 Организация раздельного учета электропотребления в квартирах',
            114 => '6.37 Смена отдельных участков наружной электропроводки. Число и сечение жил в проводе, м2: 2*1,5; 2*2,5',
            115 => '6.38 Смена отдельных участков наружной электропроводки. Число и сечение жил в проводе, м2: 3*1,5; 3*2,5',
            116 => '6.39 Смена групповых щитков',
            117 => '6.40 Выдача технических условий на переустройство и (или) перепланировку жилых помещений',
            118 => '6.41 Ремонт унитаза',
            119 => '6.42 Замена вводного крана',
            120 => '6.43 Установка водонагревателя',
            121 => '6.44 Установка электрического счетчика',
            122 => '6.45  Установка УЗО',
            123 => '6.46 Замена электрического счетчика квартир',
            124 => '6.47 Замена автомата',
            125 => '6.48 Обработка квартир от грызунов и насекомых',
            126 => '6.49 Установка индивидуального прибора учета',
            127 => '6.50 Отключения стояка отопления, холодной воды, горячей воды для ремонта сан. технического оборудования квартиры',
            128 => '6.51 Замена фильтра очистки воды',
            129 => '6.52 Замена индивидуального прибора учета',
            130 => '6.53 Замена регулятора давления',
            131 => '6.54 Установка сеточки в фильтр очистки воды',
            132 => '6.55 Прочистка фильтра тонкой очистки',
            133 => '6.56  Прочистка фильтра грубой очистки',
            134 => '7.1 Промывка ствола мусоропровода',
            135 => '7.2 Уборка подъезда',
            136 => '7.3 Обработка от грызунов (дератизация)',
            137 => '7.4 Обработка от насекомых (дезинсекция)',
            138 => '7.5 Неубранный карниз над подъездом ',
            139 => '7.6 Засор мусоропровода',
            140 => '7.7 Холод в подъезде',
            141 => '8.1 Прорыв трубы',
            142 => '8.2 Течь вводного крана',
            143 => '8.3 Течь отопительного прибора',
            144 => '8.4 Нет отопления',
            145 => '8.5 Прорыв трубы отопления после вводного крана',
            146 => '8.6 Течь отопительного прибора, при наличии вводных кранов',
            147 => '8.7 Гул в трубах, радиаторе',
            148 => '8.8 Прорыв трубы в МОП',
            149 => '8.9 Течь вводного крана в МОП',
            150 => '8.10 Течь отопительного прибора в МОП',
            151 => '8.11 Нет отопления в МОП',
            152 => '8.12 Прорыв трубы отопления после вводного крана в МОП',
            153 => '8.13 Течь отопительного прибора, при наличии вводных кранов в МОП',
            154 => '8.14 Гул в трубах, радиаторе в МОП',
            155 => '9.1 Проблемы в работе дымохода',
            156 => '9.2 Проблемы в работе вентканалов',
            157 => '9.3 Неисправность систем пожаробезопасности ',
            158 => '10.1 Засор канализационного стояка',
            159 => '10.2 Течь канализационного стояка',
            160 => '10.3 Течь канализации в подвале',
            161 => '10.4 Протечка канализационного отвода унитаза',
            162 => '10.5 Протечка канализационного отвода в ванной',
            163 => '10.6 Протечка канализационного отвода в кухне',
            164 => '10.7 Засор канализационного отвода унитаза',
            165 => '10.8 Засор канализационного отвода в ванной',
            166 => '10.9 Засор канализационного отвода в кухне',
            167 => '10.10 Запах канализации в подъезде',
            168 => '10.11 Запах канализации в подвале',
            169 => '11.1 Прорыв трубы',
            170 => '11.2 Течь вводного крана',
            171 => '11.3 Течь полотенцесушителя',
            172 => '11.4 Нет горячей воды',
            173 => '11.5 Течь смесителя в ванной',
            174 => '11.6 Течь смесителя на кухне',
            175 => '11.7 Срыв гибкой подводки в ванной',
            176 => '11.8 Срыв гибкой подводки на кухне',
            177 => '11.9 Нет напора воды в кране ГВС',
            178 => '11.10 Холодный полотенцесушитель',
            179 => '12.1 Запах горелой проводки',
            180 => '12.2 Отключение электроснабжения',
            181 => '12.3 Не горит лампочка в подъезде',
            182 => '12.4 Мерцание света',
            183 => '12.5 Искрит в эл. щите',
            184 => '12.6 Искрит выключатель/розетка',
            185 => '12.7 Выбивает автомат',
            186 => '12.8 Нагреваются провода/розетка',
            187 => '12.9 Неисправности в электроплите (с отключением всей электроплиты)',
            188 => '12.10 Неисправности в электроплите (с выходом из строя одной конфорки и жарочного шкафа)',
            189 => '13.1 Сорвало кровлю ветром',
            190 => '13.2 Протечка кровли',
            191 => '13.3 Протечка ливневой канализации',
            192 => '13.4 Неубранные сосульки, наледь и снег, свисающие с крыши и карнизов ',
            193 => '14.1 Осыпается фасад',
            194 => '14.2 Появление трещин',
            195 => '14.3 Несанкционированные надписи и рисунки на фасадах жилых зданий ',
            196 => '14.4 Отсутствие/повреждение указателей с наименованием улицы и номером дома',
            197 => '14.5 Несанкционированные объявления на фасадах жилых домов (очистка фасадов от расклеенных объявлений)',
            198 => '14.6 Ремонт водостока',
            199 => '14.7 Промазать межпанельные швы',
            200 => '15.1 Появились трещины',
            201 => '15.2 Стена осыпется',
            202 => '15.3 Стена обрушилась',
            203 => '16.1 Осыпается фундамент',
            204 => '16.2 Появление трещин',
            205 => '16.3 Проседание фундамента',
            206 => '17.1. Незаконное проживание мигрантов в местах общего пользования (подвалы, чердаки) ',
            207 => '17.2 Нарушение при выборе (смене) управляющей организации (подготовка ответа, проведение проверки)',
            208 => '17.3 Нарушение при создании ТСЖ (подготовка ответа, проведение проверки) ',
            209 => '17.4 Нарушение порядка пользования общим имуществом (подготовка ответа, проведение проверки)',
            210 => '17.5 Складирование реагентов в помещениях общего пользования',
            211 => '17.6 Нарушения при предоставлении информации от управляющей организации ',
            212 => '18.1 Составление акта осмотра жилого помещения по залитию',
            213 => '19.1 Иное'
        ];

        foreach ( $types as $mosreg_id => $type_name )
        {
            if ( preg_match( '/(\d\.\d)\ (.*)/i', $type_name, $matches ) )
            {
                $type = Type
                    ::where( 'name', 'like', '%' . $matches[ 2 ] . '%' )
                    ->first();
                if ( $type )
                {
                    dd( $type );
                }
            }
        }*/

        $managements = Management
            ::whereNotNull( 'mosreg_username' )
            ->whereNotNull( 'mosreg_password' )
            ->get();
        foreach ( $managements as $management )
        {
            $buildings = $management
                ->buildings()
                ->whereNull( 'mosreg_id' )
                ->get();
            if ( ! $buildings->count() ) continue;
            //$bar = $this->output->createProgressBar( $buildings->count() );
            $mosreg = new \App\Classes\Mosreg( $management->mosreg_username, $management->mosreg_password );
            foreach ( $buildings as $building )
            {
                //$bar->advance();
                $this->line( $building->name );
                $res = $mosreg->searchAddress( $building->name, true );
                $cnt = count( $res );
                if ( ! $cnt )
                {
                    $this->warn( 'Ничего не найдено' );
                }
                else if ( count( $res ) == 1 )
                {
                    $this->info( 'Адрес найден' );
                    $building->mosreg_id = $res[ 0 ]->addressId;
                    $building->save();
                }
                else
                {
                    $values = [];
                    foreach ( $res as $r )
                    {
                        $values[] = $r->label;
                    }
                    print_r( $values );
                    $answer = $this->anticipate('Выберите адрес', $values, 0 );
                    if ( isset( $res[ $answer ] ) )
                    {
                        $building->mosreg_id = $res[ $answer ]->addressId;
                        $building->save();
                    }
                    else
                    {
                        $this->error( 'Некорректный выбор' );
                    }
                }
            }
        }
        //$bar->finish();
    }
}