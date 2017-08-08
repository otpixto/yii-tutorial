@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Добавить обращение' ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'tickets.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="row">

        <div class="col-lg-7 col-md-6">

            <div class="form-group">
                {!! Form::label( 'type_id', 'Тип обращения', [ 'class' => 'control-label col-lg-3' ] ) !!}
                <div class="col-lg-9">
                    {!! Form::select( 'type_id', [ null => ' -- выберите из списка -- ' ] + $types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип обращения', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'address', 'Адрес обращения', [ 'class' => 'control-label col-lg-3' ] ) !!}
                <div class="col-lg-9">
                    {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control address', 'placeholder' => 'Адрес', 'required', 'id' => 'address' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'place', 'Проблемное место', [ 'class' => 'control-label col-lg-3' ] ) !!}
                <div class="col-lg-9">
                    {!! Form::select( 'place', [ null => ' -- выберите из списка -- ' ] + $places, \Input::old( 'place' ), [ 'class' => 'form-control', 'placeholder' => 'Проблемное место', 'required', 'id' => 'address' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Телефоны', [ 'class' => 'control-label col-md-2 col-lg-3' ] ) !!}
                <div class="pull-right">
                    <div class="col-md-2 col-lg-1">
                        <button type="button" class="btn btn-default" disabled="disabled" id="customers-select">
                            <i class="fa fa-user"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    {!! Form::text( 'phone', \Input::old( 'phone', \Input::get( 'phone' ) ), [ 'id' => 'phone', 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
                </div>
                <div class="col-md-4">
                    {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'id' => 'phone2', 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'ФИО', [ 'class' => 'control-label col-lg-3' ] ) !!}
                <div class="col-lg-3">
                    {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'id' => 'lastname', 'class' => 'form-control', 'placeholder' => 'Фамилия', 'required' ] ) !!}
                </div>
                <div class="col-lg-3">
                    {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'id' => 'firstname', 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                </div>
                <div class="col-lg-3">
                    {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'id' => 'middlename', 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'customer_address', 'Адрес проживания', [ 'class' => 'control-label col-lg-3' ] ) !!}
                <div class="col-lg-9">
                    {!! Form::text( 'customer_address', \Input::old( 'customer_address' ), [ 'class' => 'form-control address', 'placeholder' => 'Адрес', 'required', 'id' => 'customer_address' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-offset-3 col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        <input type="checkbox" name="emergency" value="1" />
                        <span></span>
                        Авария
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        <input type="checkbox" name="urgently" value="1" />
                        <span></span>
                        Срочно
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        <input type="checkbox" name="dobrodel" value="1" />
                        <span></span>
                        Добродел
                    </label>
                </div>
            </div>

        </div>

        <div class="col-lg-5 col-md-6">

            <div class="form-group">
                <label class="control-label col-lg-3">
                    Категория
                </label>
                <div class="col-lg-9">
                    <span class="form-control" id="category"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label col-lg-5' ] ) !!}
                <div class="col-lg-7">
                    <span class="form-control" id="season"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на принятие заявки в работу, час', [ 'class' => 'control-label col-lg-7' ] ) !!}
                <div class="col-lg-5">
                    <span class="form-control" id="period_acceptance"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на исполнение, час', [ 'class' => 'control-label col-lg-7' ] ) !!}
                <div class="col-lg-5">
                    <span class="form-control" id="period_execution"></span>
                </div>
            </div>

            <div id="managements"></div>

        </div>

    </div>

    <div class="row">

        <div class="col-md-12">

            <button type="button" class="btn btn-default margin-bottom-5" id="microphone" data-state="off">
                <i class="fa fa-microphone-slash"></i>
            </button>
            {!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
            {!! Form::textarea( 'text', \Input::old( 'text' ), [ 'class' => 'form-control autosizeme', 'placeholder' => 'Текст обращения', 'required', 'rows' => 3 ] ) !!}

        </div>

    </div>

    <div class="row margin-top-10">

        <div class="col-xs-7">

            {!! Form::label( 'tags', 'Теги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'tags', \Input::old( 'tags' ), [ 'class' => 'form-control input-large', 'data-role' => 'tagsinput' ] ) !!}

        </div>

        <div class="col-xs-5">
            {!! Form::submit( 'Добавить', [ 'class' => 'btn green btn-block btn-lg' ] ) !!}
        </div>

    </div>

    {!! Form::hidden( 'address_id', null, [ 'id' => 'address_id' ] ) !!}
    {!! Form::hidden( 'customer_address_id', null, [ 'id' => 'customer_address_id' ] ) !!}

    {!! Form::close() !!}

    <div class="modal fade bs-modal-lg" tabindex="-1" role="basic" aria-hidden="true" id="customers-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Выберите заявителя</h4>
                </div>
                <div class="modal-body" id="customers">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dark btn-outline" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/typeahead/typeahead.css" rel="stylesheet" type="text/css" />
	<link href="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
    <style>
        .mt-checkbox, .mt-radio {
            margin-bottom: 0;
        }
    </style>
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
	<script src="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js" type="text/javascript"></script>
    <script src="https://webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function SearchCustomers ( phone )
        {

            if ( phone.length < 10 ) return;

            $( '#customers-list .list-group' ).empty();

            $.get( '{{ route( 'customers.search' ) }}', {
                phone: phone
            }, function ( response )
            {
                if ( response )
                {
                    $( '#customers' ).html( response );
                    $( '#customers-select' ).removeAttr( 'disabled' ).attr( 'class', 'btn btn-warning' );
                }
                else
                {
                    $( '#customers' ).empty();
                    $( '#customers-select' ).attr( 'disabled', 'disabled' ).attr( 'class', 'btn btn-default' );
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

        function formattedPhone ( phone )
        {

            var res = '+7 (' + phone.substr( 0, 3 ) + ') ' + phone.substr( 3, 3 ) + '-' + phone.substr( 6, 2 ) + '-' + phone.substr( 8, 2 );
            return res;

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
                        url: '{{ route( 'addresses.search' ) }}?q=%QUERY',
                        wildcard: '%QUERY'
                    }
                });

                addresses.initialize();

                $( '.address' ).typeahead( null, {
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
                        suggestion: Handlebars.compile('<div><strong>\{\{name\}\}</strong></div>')
                    }
                });

                $( '#phone' ).on( 'keyup', function ( e )
                {
                    if ( /_/.test( $( this ).val() ) )
                    {
                        $( '#customers' ).empty();
                        $( '#customers-select' ).attr( 'disabled', 'disabled' ).attr( 'class', 'btn btn-default' );
                        return;
                    }
                    SearchCustomers( $( this ).val() );
                });

            })

            .on( 'click', '#customers-select', function ( e )
            {
                e.preventDefault();
                $( '#customers-modal' ).modal( 'show' );
            })

            .on( 'click', '#customers-clear', function ( e )
            {
                e.preventDefault();
                if ( ! confirm( 'Очистить поля заявителя?' ) ) return;
                $( '#firstname, #middlename, #lastname, #customer_address, #customer_address_id, #phone2' ).val( '' );
                $( this ).attr( 'id', 'customers-select' ).attr( 'class', 'btn btn-warning' );
            })

            .on( 'click', '[data-action="customers-select"]', function ( e )
            {

                e.preventDefault();

                if ( ! confirm( 'Заполнить поля заявителя выбранными данными?' ) ) return;

                $( '#customers-modal' ).modal( 'hide' );

                $( '#firstname' ).val( $( this ).attr( 'data-firstname' ) );
                $( '#middlename' ).val( $( this ).attr( 'data-middlename' ) );
                $( '#lastname' ).val( $( this ).attr( 'data-lastname' ) );
                $( '#customer_address' ).val( $( this ).attr( 'data-address' ) );

                if ( $( '#phone2' ).val() == '' )
                {
                    if ( $( '#phone' ).val().replace( /\D/g, '' ).substr( -10 ) == $( this ).attr( 'data-phone2' ) )
                    {
                        $( '#phone2' ).val( $( this ).attr( 'data-phone' ) );
                    }
                    else
                    {
                        $( '#phone2' ).val( $( this ).attr( 'data-phone2' ) );
                    }
                }

                $( '#customer_address_id' ).val( $( this ).attr( 'data-address-id' ) );

                $( '#customers-select' ).attr( 'class', 'btn btn-danger' ).attr( 'id', 'customers-clear' );

            })

            .on( 'typeahead:asyncrequest', '#address', function ( e, data )
            {
                $( '#address_id' ).val( '' ).trigger( 'change' );
            })

            .on( 'typeahead:select', '#address', function ( e, data )
            {
                $( '#address_id' ).val( data.id ).trigger( 'change' );
            })

            .on( 'typeahead:asyncrequest', '#customer_address', function ( e, data )
            {
                $( '#customer_address_id' ).val( '' );
            })

            .on( 'typeahead:select', '#customer_address', function ( e, data )
            {
                $( '#customer_address_id' ).val( data.id );
            })

            .on( 'change', '#type_id', function ( e )
            {
                var type_id = $( '#type_id' ).val();
                if ( !type_id )
                {
                    $( '#type_info' ).addClass( 'hidden' );
                    return;
                };
                $.post( '{{ route( 'types.search' ) }}', {
                    type_id: type_id
                }, function ( response )
                {
                    $( '#period_acceptance' ).text( response.period_acceptance );
                    $( '#period_execution' ).text( response.period_execution );
                    $( '#season' ).text( response.season );
                    $( '#category' ).text( response.category_name );
                });
            })

            .on( 'change', '#address_id, #type_id', function ( e )
            {
                var address_id = $( '#address_id' ).val();
                var type_id = $( '#type_id' ).val();
                if ( !address_id || !type_id )
                {
                    $( '#management' ).addClass( 'hidden' );
                    return;
                };
                $.post( '{{ route( 'managements.search' ) }}', {
                    address_id: address_id,
                    type_id: type_id
                }, function ( response )
                {
                    $( '#managements' ).html( response );
                });
            });

    </script>
@endsection