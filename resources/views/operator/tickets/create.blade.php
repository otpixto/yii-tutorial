@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Добавить обращение' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light ">
        <td class="portlet-body">

            <h1 class="margin-top-10 margin-bottom-30">
                <i class="fa fa-plus-square text-success"></i>
                Регистрация обращения
            </h1>

            {!! Form::open( [ 'url' => route( 'tickets.store' ) ] ) !!}

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', \Input::old( 'phone', \Input::get( 'phone' ) ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row hidden" id="customers-list">
                <div class="col-md-12">
                    <div class="alert alert-info hidden">
                        <a href="#" data-action="customer-cancel" class="text-danger">
                            <i class="fa fa-remove"></i>
                            отменить выбор заявителя
                        </a>
                    </div>
                    <div class="alert alert-info">
                        <h4 class="block">
                            Возможно это:
                            <a href="#" class="btn btn-danger btn-xs pull-right" data-action="customer-close">
                                Нет
                            </a>
                        </h4>
                        <div class="list-group" style="margin-bottom: 0px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>
                </div>

            </div>

            <hr />

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'type_id', 'Тип обращения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'type_id', $types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип обращения', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label( 'address', 'Адрес обращения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес', 'required', 'id' => 'address' ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row hidden" id="management">

                <div class="col-md-12">

                    <div class="alert alert-info">

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label( null, 'Наименование УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_name">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label( null, 'Телефон УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_phone">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label( null, 'Адрес УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_address">
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <hr />

            <div class="row">

                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
                        <button type="button" class="btn btn-xs btn-default pull-right" id="microphone" data-state="off">
                            <i class="fa fa-microphone-slash"></i>
                        </button>
                        {!! Form::textarea( 'text', \Input::old( 'text' ), [ 'class' => 'form-control autosizeme', 'placeholder' => 'Текст обращения', 'required', 'rows' => 5 ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row margiv-top-10">
                <div class="col-md-12">
                    {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                </div>
            </div>

            {!! Form::close() !!}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/typeahead/typeahead.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/typeahead/handlebars.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/typeahead/typeahead.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-form-tools.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="https://webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();

        function SearchCustomers ( phone )
        {

            if ( phone.length < 10 ) return;

            $( '#customers-list .list-group' ).empty();

            $.get( '{{ route( 'customer.search' ) }}', {
                phone: phone
            }, function ( response )
            {
                if ( response.length )
                {
                    $( '#customers-list' ).removeClass( 'hidden' );
                    $.each( response, function ( i, customer )
                    {
                        $( '#customers-list .list-group' ).append(
                            $( '<a href="#" class="list-group-item" data-action="set-customer"></a>' )
                                .attr( 'data-id', customer.id )
                                .attr( 'data-firstname', customer.firstname )
                                .attr( 'data-middlename', customer.middlename )
                                .attr( 'data-lastname', customer.lastname )
                                .text( customer.full_name )
                                .append(
                                    $( '<i class="fa fa-arrow-circle-down pull-right text-success"></i>' )
                                )
                        );
                    });
                }
                else
                {
                    $( '#customers-list' ).addClass( 'hidden' );
                }
            });

        };

        function MicrophoneOn ()
        {

            if ( $( '#microphone' ).attr( 'data-state' ) == 'on' ) return;

            $( '#microphone' )
                .attr( 'data-state', 'on' )
                .removeClass( 'btn-default' )
                .addClass( 'btn-success' )
                .find( '.fa' )
                .removeClass( 'fa-microphone-slash' )
                .addClass( 'fa-microphone' );

            streamer.start({
                // initCallback вызывается после успешной инициализации сессии.
                initCallback: function () {
                    console.log("Началась запись звука.");
                },
                // Данная функция вызывается многократно.
                // Ей передаются промежуточные результаты распознавания.
                // После остановки распознавания этой функции
                // будет передан финальный результат.
                dataCallback: function (text, done, merge, words, biometry) {
                    console.log("Распознанный текст: " + text);
                    console.log("Является ли результат финальным:" + done);
                    console.log("Число обработанных запросов, по которым выдан ответ от сервера: " + merge);
                    console.log("Подробная информаия о распознанных фрагметах: " + words);
                    // Подробнее о массиве biometry см. в разделе Анализ речи.
                    $.each(biometry, function (j, bio) {
                        console.log("Характеристика: " + bio.tag + " Вариант: " + bio.class +
                            " Вероятность: " + bio.confidence.toFixed(3));
                    });
                },
                // Вызывается при возникновении ошибки (например, если передан неверный API-ключ).
                errorCallback: function (err) {
                    console.log("Возникла ошибка: " + err);
                    alert( "Возникла ошибка: " + err );
                    MicrophoneOff();
                },
                // Содержит сведения о ходе процесса распознавания.
                infoCallback: function (sent_bytes, sent_packages, processed, format) {
                    console.log("Отправлено данных на сервер: " + sent_bytes);
                    console.log("Отправлено пакетов на сервер: " + sent_packages);
                    console.log("Количество пакетов, которые обработал сервер: " + processed);
                    console.log("До какой частоты понижена частота дискретизации звука: " + format);
                },
                // Будет вызвана после остановки распознавания.
                stopCallback: function () {
                    console.log("Запись звука прекращена.");
                },
                // Возвращать ли промежуточные результаты.
                particialResults: true,
                // Длительность промежутка тишины (в сантисекундах),
                // при наступлении которой API начнет преобразование
                // промежуточных результатов в финальный текст.
                utteranceSilence: 60
            });

        };

        function MicrophoneOff ()
        {

            if ( $( '#microphone' ).attr( 'data-state' ) == 'off' ) return;

            $( '#microphone' )
                .attr( 'data-state', 'off' )
                .removeClass( 'btn-success' )
                .addClass( 'btn-default' )
                .find( '.fa' )
                .removeClass( 'fa-microphone' )
                .addClass( 'fa-microphone-slash' );

            streamer.stop();

        };

        function ToggleMicrophone ()
        {

            if ( $( this ).attr( 'data-state' ) == 'off' )
            {

                MicrophoneOn();

            }
            else
            {

                MicrophoneOff();

            }

        };

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '#microphone' ).click( ToggleMicrophone );

                $( '.select2' ).select2();

                var addresses = new Bloodhound({
                    datumTokenizer: function(d) { return d.tokens; },
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route( 'address.search' ) }}?q=%QUERY',
                        wildcard: '%QUERY'
                    }
                });

                addresses.initialize();

                $( '#address' ).typeahead( null, {
                    name: 'address',
                    displayKey: 'name',
                    hint: ( App.isRTL() ? false : true ),
                    highlight: true,
                    minLength: 3,
                    source: addresses.ttAdapter(),
                    templates: {
                        empty: [
                            '<div class="alert alert-danger" style="margin-bottom: 0;">',
                            'Ничего не найдено по вашему запросу',
                            '</div>'
                        ].join('\n'),
                        suggestion: Handlebars.compile('<div><strong>\{\{name\}\}</strong> - <i class="small">\{\{management_name\}\}</i></div>')
                    }
                });

                $( '#phone' ).on( 'keyup', function ( e )
                {
                    if ( /_/.test( $( this ).val() ) ) return;
                    SearchCustomers( $( this ).val() );
                });

            })

            .on( 'click', '[data-action="set-customer"]', function ( e )
            {
                e.preventDefault();
                $( '#firstname' ).val( $( this ).attr( 'data-firstname' ) ).attr( 'readonly', 'readonly' );
                $( '#middlename' ).val( $( this ).attr( 'data-middlename' ) ).attr( 'readonly', 'readonly' );
                $( '#lastname' ).val( $( this ).attr( 'data-lastname' ) ).attr( 'readonly', 'readonly' );
                $( '#customers-list .alert' ).toggleClass( 'hidden' );
            })

            .on( 'click', '[data-action="customer-cancel"]', function ( e )
            {
                e.preventDefault();
                $( '#firstname, #middlename, #lastname' ).val( '' ).removeAttr( 'readonly' );
                $( '#customers-list .alert' ).toggleClass( 'hidden' );
            })

            .on( 'click', '[data-action="customer-close"]', function ( e )
            {
                e.preventDefault();
                $( '#customers-list' ).addClass( 'hidden' );
            })

            .on( 'typeahead:asyncrequest', '#address', function ( e, data )
            {
                $( '#management' ).addClass( 'hidden' );
                $( '#management_name, #management_address, #management_phone' ).text( '' );
            })

            .on( 'typeahead:select', '#address', function ( e, data )
            {
                $( '#management' ).removeClass( 'hidden' );
                $( '#management_name' ).text( data.management_name || '' );
                $( '#management_address' ).text( data.management_address || '' );
                $( '#management_phone' ).text( data.management_phone || '' );
            });

    </script>
@endsection