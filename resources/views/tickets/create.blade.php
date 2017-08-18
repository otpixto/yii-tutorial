@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'tickets.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="row">

        <div class="col-lg-7">

            <div class="form-group">
                {!! Form::label( 'type_id', 'Тип обращения', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'type_id', [ null => ' -- выберите из списка -- ' ] + $types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип обращения', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'address_id', 'Адрес обращения', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::select( 'address_id', \Input::old( 'address_id' ) ? \App\Models\Address::find( \Input::old( 'address_id' ) )->pluck( 'name', 'id' ) : [], \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес обращения', 'data-allow-clear' => true, 'required' ] ) !!}
                </div>
                {!! Form::label( 'flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'flat', \Input::old( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв. \ Офис', 'id' => 'flat' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'place', 'Проблемное место', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'place', [ null => ' -- выберите из списка -- ' ] + $places, \Input::old( 'place' ), [ 'class' => 'form-control', 'placeholder' => 'Проблемное место', 'required', 'id' => 'place' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, '&nbsp;', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency' ) ) !!}
                        <span></span>
                        Авария
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'urgently', 1, \Input::old( 'urgently' ) ) !!}
                        <span></span>
                        Срочно
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'dobrodel', 1, \Input::old( 'dobrodel' ) ) !!}
                        <span></span>
                        Добродел
                    </label>
                </div>
            </div>

            <hr style="margin-top: 26px;" />

            <div class="form-group">
                {!! Form::label( null, 'Телефоны', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-4">
                    {!! Form::text( 'phone', \Input::old( 'phone', \Input::get( 'phone' ) ), [ 'id' => 'phone', 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'id' => 'phone2', 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
                </div>
                <div class="col-xs-1 text-right">
                    @if ( ! empty( \Input::old( 'customer_id' ) ) )
                        <button type="button" class="btn btn-danger" id="customers-clear">
                            <i class="fa fa-user"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-default" disabled="disabled" id="customers-select">
                            <i class="fa fa-user"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'ФИО', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'id' => 'lastname', 'class' => 'form-control', 'placeholder' => 'Фамилия', 'required' ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'id' => 'firstname', 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'id' => 'middlename', 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'customer_address_id', 'Адрес проживания', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::select( 'actual_address_id', \Input::old( 'actual_address_id' ) ? \App\Models\Address::find( \Input::old( 'actual_address_id' ) )->pluck( 'name', 'id' ) : [], \Input::old( 'actual_address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес проживания', 'data-allow-clear' => true, 'required', 'id' => 'actual_address_id' ] ) !!}
                </div>
                {!! Form::label( 'actual_flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'actual_flat', \Input::old( 'actual_flat' ), [ 'class' => 'form-control', 'placeholder' => 'Квартира', 'required', 'id' => 'actual_flat' ] ) !!}
                </div>
            </div>

        </div>

        <div class="col-lg-5">

            <hr class="visible-sm" />

            <div class="form-group">
                <label class="control-label col-xs-3">
                    Категория
                </label>
                <div class="col-xs-9">
                    <span class="form-control" id="category"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label col-xs-5' ] ) !!}
                <div class="col-xs-7">
                    <span class="form-control" id="season"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на принятие заявки в работу, час', [ 'class' => 'control-label col-xs-7' ] ) !!}
                <div class="col-xs-5">
                    <span class="form-control" id="period_acceptance"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на исполнение, час', [ 'class' => 'control-label col-xs-7' ] ) !!}
                <div class="col-xs-5">
                    <span class="form-control" id="period_execution"></span>
                </div>
            </div>

            <div id="managements"></div>

        </div>

    </div>

    <hr />

    <div class="row">

        <div class="col-xs-12">

            <button type="button" class="btn btn-default margin-bottom-5" id="microphone" data-state="off">
                <i class="fa fa-microphone-slash"></i>
            </button>
            {!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
            {!! Form::textarea( 'text', \Input::old( 'text' ), [ 'class' => 'form-control autosizeme', 'placeholder' => 'Текст обращения', 'required', 'rows' => 5 ] ) !!}

        </div>

    </div>

    <div class="row margin-top-10">

        <div class="col-xs-7">

            {!! Form::label( 'tags', 'Теги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'tags', \Input::old( 'tags' ), [ 'class' => 'form-control input-large', 'data-role' => 'tagsinput' ] ) !!}

        </div>

        <div class="col-xs-5">
            <button type="submit" class="btn green btn-block btn-lg">
                <i class="fa fa-plus"></i>
                Добавить обращение
            </button>
        </div>

    </div>

    {!! Form::hidden( 'customer_id', \Input::old( 'customer_id' ), [ 'id' => 'customer_id' ] ) !!}
    {!! Form::hidden( 'selected_managements', implode( ',', \Input::old( 'managements', [] ) ), [ 'id' => 'selected_managements' ] ) !!}

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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
    <style>
        .mt-checkbox, .mt-radio {
            margin-bottom: 0;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-form-tools.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js" type="text/javascript"></script>
    <script src="//webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();

        function SearchCustomers ()
        {

            var phone = $( '#phone' ).val();

            if ( /_/.test( phone ) )
            {
                $( '#customers' ).empty();
                $( '#customers-select' ).attr( 'disabled', 'disabled' ).attr( 'class', 'btn btn-default' );
                return;
            }

            //if ( phone.length < 10 ) return;

            $( '#customers-list .list-group' ).empty();

            $.get( '{{ route( 'customers.search' ) }}', {
                phone: phone
            }, function ( response )
            {
                if ( response )
                {
                    $( '#customers' ).html( response );
                    if ( $( '#customer_id' ).val() == '' )
                    {
                        $( '#customers-select' ).removeAttr( 'disabled' ).attr( 'class', 'btn btn-warning' );
                        if ( $( '[data-action="customers-select"]' ).length == 1 )
                        {
                            $( '[data-action="customers-select"]' ).trigger( 'click' );
                        }
                    }
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

            streamer.start({

                apikey: '7562b975-c851-4ab9-b12e-c017b93ea567',

                // initCallback вызывается после успешной инициализации сессии.
                initCallback: function () {
                    $( '#microphone' )
                        .attr( 'data-state', 'on' )
                        .removeClass( 'btn-default' )
                        .addClass( 'btn-success' )
                        .find( '.fa' )
                        .removeClass( 'fa-microphone-slash' )
                        .addClass( 'fa-microphone' );
                },
                // Данная функция вызывается многократно.
                // Ей передаются промежуточные результаты распознавания.
                // После остановки распознавания этой функции
                // будет передан финальный результат.
                dataCallback: function ( text, done )
                {
                    if ( done && text != '' )
                    {
                        MicrophoneOff();
                        var value = $.trim( $( '#text' ).val() );
                        if ( value != '' )
                        {
                            value += "\n";
                        }
                        $( '#text' ).val( value + text );
                    }
                },
                // Вызывается при возникновении ошибки (например, если передан неверный API-ключ).
                errorCallback: function (err) {
                    //console.log("Возникла ошибка: " + err);
                    alert( "Возникла ошибка: " + err );
                    MicrophoneOff();
                },
                // Содержит сведения о ходе процесса распознавания.
                infoCallback: function (sent_bytes, sent_packages, processed, format) {
                    //console.log("Отправлено данных на сервер: " + sent_bytes);
                    //console.log("Отправлено пакетов на сервер: " + sent_packages);
                    //console.log("Количество пакетов, которые обработал сервер: " + processed);
                    //console.log("До какой частоты понижена частота дискретизации звука: " + format);
                },
                // Будет вызвана после остановки распознавания.
                stopCallback: function () {
                    //console.log("Запись звука прекращена.");
                    MicrophoneOff();
                },
                // Возвращать ли промежуточные результаты.
                particialResults: true,
                // Длительность промежутка тишины (в сантисекундах),
                // при наступлении которой API начнет преобразование
                // промежуточных результатов в финальный текст.
                utteranceSilence: 200
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

            if ( $( '#microphone' ).attr( 'data-state' ) == 'off' )
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

        function GetTypeInfo ()
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

        };

        function GetManagements ()
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
                type_id: type_id,
                selected: $( '#selected_managements' ).val()
            }, function ( response )
            {
                $( '#managements' ).html( response );
            });

        };

        $( document )

            .ready( function ()
            {

                GetTypeInfo();
                GetManagements();
                SearchCustomers();

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '#microphone' ).click( ToggleMicrophone );

                $( '.select2' ).select2();
                
                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

            })

            .on( 'keyup', '#phone', function ( e )
            {
                SearchCustomers();
            })

            .on( 'keydown', function ( e )
            {
                if ( e.ctrlKey && e.which == 32 )
                {
                    ToggleMicrophone();
                }
            })

            .on( 'click', '#customers-select', function ( e )
            {
                e.preventDefault();
                $( '#customers-modal' ).modal( 'show' );
            })

            .on( 'click', '#customers-clear', function ( e )
            {

                e.preventDefault();

                var that = $( this );

                bootbox.confirm({
                    message: 'Очистить поля заявителя?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            $( '#firstname, #middlename, #lastname, #customer_id, #actual_address_id, #phone2, #actual_flat' ).val( '' );
                            $( '#actual_address_id' ).trigger( 'change' );
                            $( '#phone' ).removeAttr( 'readonly' );
                            that.attr( 'id', 'customers-select' ).attr( 'class', 'btn btn-warning' );

                        }
                    }
                });

            })

            .on( 'click', '[data-action="customers-select"]', function ( e )
            {

                e.preventDefault();

                var that = $( this );

                function setValue ()
                {

                    $( '#customers-modal' ).modal( 'hide' );

                    var address_id = that.attr( 'data-address-id' );
                    var address = that.attr( 'data-address' );

                    $( '#firstname' ).val( that.attr( 'data-firstname' ) );
                    $( '#middlename' ).val( that.attr( 'data-middlename' ) );
                    $( '#lastname' ).val( that.attr( 'data-lastname' ) );
                    $( '#actual_address_id' ).append( $( '<option></option>' ).val( address_id ).text( address ) ).val( address_id ).trigger( 'change' );
                    $( '#actual_flat' ).val( that.attr( 'data-flat' ) );

                    $( '#phone' ).attr( 'readonly', 'readonly' );

                    if ( $( '#phone2' ).val() == '' )
                    {
                        if ( $( '#phone' ).val().replace( /\D/g, '' ).substr( -10 ) == that.attr( 'data-phone2' ) )
                        {
                            $( '#phone2' ).val( that.attr( 'data-phone' ) );
                        }
                        else
                        {
                            $( '#phone2' ).val( that.attr( 'data-phone2' ) );
                        }
                    }

                    $( '#customer_id' ).val( that.attr( 'data-id' ) );

                    $( '#customers-select' ).attr( 'class', 'btn btn-danger' ).attr( 'id', 'customers-clear' );

                };

                if ( $( '[data-action="customers-select"]' ).length == 1 )
                {
                    setValue();
                }
                else
                {
                    bootbox.confirm({
                        message: 'Заполнить поля заявителя выбранными данными?',
                        size: 'small',
                        buttons: {
                            confirm: {
                                label: '<i class="fa fa-check"></i> Да',
                                className: 'btn-success'
                            },
                            cancel: {
                                label: '<i class="fa fa-times"></i> Нет',
                                className: 'btn-danger'
                            }
                        },
                        callback: function ( result )
                        {
                            if ( result )
                            {

                                setValue();

                            }
                        }
                    });
                }

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
                GetTypeInfo();
            })

            .on( 'change', '#address_id, #type_id', function ( e )
            {
                GetManagements();
            });

    </script>
@endsection