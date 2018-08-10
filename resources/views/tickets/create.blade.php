@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @include( 'tickets.parts.create' )

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
	<link href="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
    <style>
        .mt-checkbox, .mt-radio {
            margin-bottom: 0;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-form-tools.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js" type="text/javascript"></script>
    <script src="https://webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();
        var timers = {};

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
                $( '#info-block' ).removeClass( 'hidden' );
                $( '#period_acceptance' ).text( response.period_acceptance + ' ч.' );
                $( '#period_execution' ).text( response.period_execution + ' ч.' );
                $( '#season' ).text( response.season || '-' );
                $( '#category' ).text( response.category_name );
                if ( response.emergency )
                {
                    $( '#emergency' ).prop( 'checked', 'checked' ).attr( 'disabled', 'disabled' ).trigger( 'change' );
                }
                else
                {
                    $( '#emergency' ).removeAttr( 'checked' ).removeAttr( 'disabled' ).trigger( 'change' );
                }
                if ( response.description )
                {
                    $( '#types-description' )
                        .removeClass( 'hidden' )
                        .text( response.description )
                        .pulsate({
                            repeat: 3,
                            speed: 500,
                            color: '#F1C40F',
                            glow: true,
                            reach: 15
                        });
                }
                else
                {
                    $( '#types-description' ).addClass( 'hidden' ).empty();
                }
            });

        };

        function GetSelect ()
        {
            if ( ! $( '#type_id' ).val() || ! $( '#building_id' ).val() ) return;
            $( '#select' ).loading();
            if ( timers[ 'select' ] )
            {
                window.clearTimeout( timers[ 'select' ] );
            }
            timers[ 'select' ] = window.setTimeout( function ()
            {
                timers[ 'select' ] = null;
                $.post( '{{ route( 'tickets.select', $ticket->id ) }}', function ( response )
                {
                    $( '#select' ).html( response );
                });
            }, 600 );
        };

        $( document )

            .ready( function ()
            {

                $( '.customer-autocomplete' ).autocomplete({
                    source: function ( request, response )
                    {
                        var r = {};
                        r.param = this.element[0].name;
                        r.value = request.term;
                        $.post( '{{ route( 'customers.search' ) }}', r, function ( data )
                        {
                            response( data );
                        });
                    },
                    minLength: 2,
                    select: function ( event, ui )
                    {
                        $( this ).trigger( 'change' );
                    }
                });

                $( '#tags' ).on( 'itemAdded', function ( e )
                {
                    var tag = e.item;
                    if ( ! tag ) return;
                    $.post( '{{ route( 'tickets.tags.add', $ticket->id ) }}', {
                        tag: tag
                    });
                });

                $( '#tags' ).on( 'itemRemoved', function ( e )
                {
                    var tag = e.item;
                    if ( ! tag ) return;
                    $.post( '{{ route( 'tickets.tags.del', $ticket->id ) }}', {
                        tag: tag
                    });
                });

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '#microphone' ).click( ToggleMicrophone );

                GetTypeInfo();
                GetSelect();

                $( '#phone' ).trigger( 'change' );

            })

            .on( 'click', '[data-action="create_another"]', function ()
            {
                $( '#create_another' ).val( '1' );
                this.form.submit();
            })

            .on ( 'click', '.nav-tabs a', function ( e )
            {
                $( this ).tab( 'show' );
                switch ( $( this ).attr( 'href' ) )
                {

                    case '#customer_tickets':
                        $( '#customer_tickets' ).loading();
                        $.get( '{{ route( 'tickets.customers', $ticket->id ) }}', function ( response )
                        {
                            $( '#customer_tickets' ).html( response );
                        });
                        break;

                    case '#neighbors_tickets':
                        $( '#neighbors_tickets' ).loading();
                        $.get( '{{ route( 'tickets.neighbors', $ticket->id ) }}', function ( response )
                        {
                            $( '#neighbors_tickets' ).html( response );
                        });
                        break;

                    case '#works':
                        $( '#works' ).loading();
                        $.get( '{{ route( 'tickets.works', $ticket->id ) }}', function ( response )
                        {
                            $( '#works' ).html( response );
                        });
                        break;

                }
            })

            .on( 'change', '#provider_id', function ( e )
            {
                $( '#building_id, #flat, #actual_building_id, #actual_flat' ).val( '' ).trigger( 'change' );
            })

            .on( 'change', '.autosave', function ( e )
            {
                var that = $( this );
                if ( timers[ that.attr( 'name' ) ] )
                {
                    window.clearTimeout( timers[ that.attr( 'name' ) ] );
                }
                timers[ that.attr( 'name' ) ] = window.setTimeout( function ()
                {
                    timers[ that.attr( 'name' ) ] = null;
                    var field = that.attr( 'name' );
                    var value = that.is( '[type="checkbox"]' ) ? ( that.is( ':checked' ) ? 1 : 0 ) : that.val();
                    $.post( '{{ route( 'tickets.save', $ticket->id ) }}', {
                        field: field,
                        value: value
                    });
                }, 500 );
            })

            .on( 'keydown', function ( e )
            {
                if ( e.ctrlKey && e.which == 32 )
                {
                    ToggleMicrophone();
                }
            })

            .on( 'change', '#type_id', function ( e )
            {
                GetTypeInfo();
            })

            .on( 'change', '#building_id, #type_id, #phone', function ( e )
            {
                GetSelect();
            })

            .on( 'change', '#fistname, #middlename, #lastname', function ( e )
            {
                var param = 'phone_by_name';
                if ( timers[ param ] )
                {
                    window.clearTimeout( timers[ param ] );
                }
                timers[ param ] = window.setTimeout( function ()
                {
                    timers[ param ] = null;
                    var firstname = $.trim( $( '#firstname' ).val() );
                    var middlename = $.trim( $( '#middlename' ).val() );
                    var lastname = $.trim( $( '#lastname' ).val() );
                    if ( firstname != '' && middlename != '' && lastname != '' )
                    {
                        var r = {};
                        r.param = param;
                        r.firstname = firstname;
                        r.middlename = middlename;
                        r.lastname = lastname;
                        $.post( '{{ route( 'customers.search' ) }}', r, function ( response )
                        {
                            if ( ! response ) return;
                            var phone = $.trim( $( '#phone' ).val().replace( '/\D/', '' ) );
                            var building_id = $( '#building_id' ).val();
                            var flat = $( '#flat' ).val();
                            var actual_building_id = $( '#actual_building_id' ).val();
                            var actual_flat = $( '#actual_flat' ).val();
                            if ( ! phone && response.phone )
                            {
                                $( '#phone' )
                                    .val( response.phone )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! building_id && response.actual_building_id )
                            {
                                $( '#building_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_building_id )
                                            .text( response.actual_building.name )
                                    )
                                    .val( response.actual_building_id )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! flat && response.actual_flat )
                            {
                                $( '#flat' )
                                    .val( response.actual_flat )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! actual_building_id && response.actual_building_id )
                            {
                                $( '#actual_building_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_building_id )
                                            .text( response.actual_building.name )
                                    )
                                    .val( response.actual_building_id )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! actual_flat && response.actual_flat )
                            {
                                $( '#actual_flat' )
                                    .val( response.actual_flat )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                        });
                    }
                }, 500 );
            })

            .on( 'change', '#phone', function ()
            {
                var param = 'name_by_phone';
                if ( timers[ param ] )
                {
                    window.clearTimeout( timers[ param ] );
                }
                timers[ param ] = window.setTimeout( function ()
                {
                    timers[ param ] = null;
                    var firstname = $.trim( $( '#firstname' ).val() );
                    var middlename = $.trim( $( '#middlename' ).val() );
                    var lastname = $.trim( $( '#lastname' ).val() );
                    var phone = $.trim( $( '#phone' ).val().replace( '/\D/', '' ) );
                    var building_id = $( '#building_id' ).val();
                    var flat = $( '#flat' ).val();
                    var actual_building_id = $( '#actual_building_id' ).val();
                    var actual_flat = $( '#actual_flat' ).val();
                    if ( ! firstname || ! middlename || ! lastname || ! actual_building_id || ! actual_flat )
                    {
                        var r = {};
                        r.param = param;
                        r.phone = phone;
                        $.post( '{{ route( 'customers.search' ) }}', r, function ( response )
                        {
                            if ( ! response ) return;
                            if ( ! firstname && response.firstname )
                            {
                                $( '#firstname' )
                                    .val( response.firstname )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! middlename && response.middlename )
                            {
                                $( '#middlename' )
                                    .val( response.middlename )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! lastname && response.lastname )
                            {
                                $( '#lastname' )
                                    .val( response.lastname )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! building_id && response.actual_building_id )
                            {
                                $( '#building_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_building_id )
                                            .text( response.actual_building.name )
                                    )
                                    .val( response.actual_building_id )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! flat && response.actual_flat )
                            {
                                $( '#flat' )
                                    .val( response.actual_flat )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! actual_building_id && response.actual_building_id )
                            {
                                $( '#actual_building_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_building_id )
                                            .text( response.actual_building.name )
                                    )
                                    .val( response.actual_building_id )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                            if ( ! actual_flat && response.actual_flat )
                            {
                                $( '#actual_flat' )
                                    .val( response.actual_flat )
                                    .trigger( 'change' )
                                    .pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                            }
                        });
                    }
                }, 500 );
            });

    </script>
@endsection