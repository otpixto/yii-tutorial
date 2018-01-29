@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'tickets.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
	{!! Form::hidden( 'draft', '0', [ 'id' => 'draft', 'autocomplete' => 'off' ] ) !!}
    {!! Form::hidden( 'ticket_id', $draft->id ?? null, [ 'id' => 'ticket_id', 'autocomplete' => 'off' ] ) !!}

    <div class="row">

        <div class="col-lg-7">

            <div class="form-group">
                {!! Form::label( 'type_id', 'Тип заявки', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'type_id', [ null => ' -- выберите из списка -- ' ] + $types, \Input::old( 'type_id', $draft->type_id ?? null ), [ 'class' => 'form-control select2 autosave', 'placeholder' => 'Тип заявки', 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            @if ( $regions->count() > 1 )
                <div class="form-group">
                    {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::select( 'region_id', $regions, \Input::old( 'region_id', $draft->region_id ?? null ), [ 'class' => 'form-control select2 autosave', 'placeholder' => 'Регион', 'data-placeholder' => 'Регион', 'required', 'autocomplete' => 'off' ] ) !!}
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label( 'address_id', 'Адрес проблемы', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::select( 'address_id', \Input::old( 'address_id', $draft->address_id ?? null ) ? \App\Models\Address::find( \Input::old( 'address_id', $draft->address_id ?? null ) )->pluck( 'name', 'id' ) : [], \Input::old( 'address_id', $draft->address_id ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес проблемы', 'data-allow-clear' => true, 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
                {!! Form::label( 'flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'flat', \Input::old( 'flat', $draft->flat ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Кв. \ Офис', 'id' => 'flat', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'place_id', 'Проблемное место', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'place_id', [ null => ' -- выберите из списка -- ' ] + $places, \Input::old( 'place_id', $draft->place_id ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Проблемное место', 'required', 'id' => 'place_id', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, '&nbsp;', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $draft->emergency ?? null ), [ 'class' => 'autosave', 'id' => 'emergency', 'autocomplete' => 'off' ] ) !!}
                        <span></span>
                        Авария
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'urgently', 1, \Input::old( 'urgently', $draft->urgently ?? null ), [ 'class' => 'autosave', 'autocomplete' => 'off' ] ) !!}
                        <span></span>
                        Срочно
                    </label>
                </div>
                <div class="col-xs-3">
                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                        {!! Form::checkbox( 'dobrodel', 1, \Input::old( 'dobrodel', $draft->dobrodel ?? null ), [ 'class' => 'autosave', 'autocomplete' => 'off' ] ) !!}
                        <span></span>
                        Добродел
                    </label>
                </div>
            </div>

            <hr style="margin-top: 30px;" />

            <div class="form-group ">
                {!! Form::label( null, 'ФИО', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'lastname', \Input::old( 'lastname', $draft->lastname ?? null ), [ 'id' => 'lastname', 'class' => 'form-control text-capitalize autosave customer-autocomplete', 'placeholder' => 'Фамилия', 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::text( 'firstname', \Input::old( 'firstname', $draft->firstname ?? null ), [ 'id' => 'firstname', 'class' => 'form-control text-capitalize autosave customer-autocomplete', 'placeholder' => 'Имя', 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::text( 'middlename', \Input::old( 'middlename', $draft->middlename ?? null ), [ 'id' => 'middlename', 'class' => 'form-control text-capitalize autosave customer-autocomplete', 'placeholder' => 'Отчество', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'phone', \Input::old( 'phone', $draft->phone ?? null ), [ 'id' => 'phone', 'class' => 'form-control mask_phone autosave customer-autocomplete', 'placeholder' => 'Телефон', 'required', $draft->customer_id ? 'readonly' : '', 'autocomplete' => 'off' ] ) !!}
                </div>
                {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'phone2', \Input::old( 'phone2', $draft->phone2 ?? null ), [ 'id' => 'phone2', 'class' => 'form-control mask_phone autosave customer-autocomplete', 'placeholder' => 'Доп. телефон', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'actual_address_id', 'Адрес проживания', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::select( 'actual_address_id', \Input::old( 'actual_address_id', $draft->actual_address_id ?? null ) ? \App\Models\Address::find( \Input::old( 'actual_address_id', $draft->actual_address_id ?? null ) )->pluck( 'name', 'id' ) : [], \Input::old( 'actual_address_id', $draft->actual_address_id ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес проживания', 'data-allow-clear' => true, 'id' => 'actual_address_id', 'autocomplete' => 'off' ] ) !!}
                </div>
                {!! Form::label( 'actual_flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::text( 'actual_flat', \Input::old( 'actual_flat', $draft->actual_flat ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Квартира', 'id' => 'actual_flat', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

        </div>

        <div class="col-lg-5 hidden" id="info-block">

            <hr class="visible-sm" />

            <div class="form-group">
                {!! Form::label( null, 'Категория', [ 'class' => 'control-label col-md-5 col-xs-6 text-muted' ] ) !!}
                <div class="col-md-7 col-xs-6">
                    <span class="form-control-static bold text-info" id="category"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label col-md-5 col-xs-6 text-muted' ] ) !!}
                <div class="col-md-7 col-xs-6">
                    <span class="form-control-static bold text-info" id="season"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на принятие заявки в работу', [ 'class' => 'control-label col-md-7 col-xs-6 text-muted' ] ) !!}
                <div class="col-md-5 col-xs-6">
                    <span class="form-control-static bold text-info" id="period_acceptance"></span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Период на исполнение', [ 'class' => 'control-label col-md-7 col-xs-6 text-muted' ] ) !!}
                <div class="col-md-5 col-xs-6">
                    <span class="form-control-static bold text-info" id="period_execution"></span>
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
            {!! Form::textarea( 'text', \Input::old( 'text', $draft->text ?? null ), [ 'class' => 'form-control autosizeme autosave', 'placeholder' => 'Текст обращения', 'required', 'rows' => 5, 'autocomplete' => 'off' ] ) !!}

        </div>

    </div>

    <div class="row margin-top-10">

        <div class="col-xs-7">

            {!! Form::label( 'tags', 'Теги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'tags', \Input::old( 'tags', $draft->tags->implode( 'text', ',' ) ), [ 'class' => 'form-control input-large', 'data-role' => 'tagsinput', 'autocomplete' => 'off' ] ) !!}

        </div>

        <div class="col-xs-5 text-right">
            <button type="submit" class="btn green btn-lg btn-block">
                <i class="fa fa-plus"></i>
                Добавить заявку
            </button>
            @if ( $draft )
                <div class="text-right margin-top-10">
                    <a href="{{ route( 'tickets.cancel', $draft->id ) }}" class="btn btn-danger" data-confirm="Вы уверены, что хотите отменить заявку?">
                        <i class="fa fa-remove"></i>
                        Отменить
                    </a>
                </div>
            @endif
        </div>

    </div>

    {!! Form::hidden( 'customer_id', \Input::old( 'customer_id', $draft->customer_id ?? null ), [ 'id' => 'customer_id', 'class' => 'autosave', 'autocomplete' => 'off' ] ) !!}
    {!! Form::hidden( 'selected_managements', implode( ',', \Input::old( 'managements', [] ) ), [ 'id' => 'selected_managements', 'autocomplete' => 'off' ] ) !!}

    {!! Form::close() !!}

    <div class="modal fade bs-modal-lg" tabindex="-1" role="basic" aria-hidden="true" id="customers-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">
                        Выберите заявителя
                    </h4>
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

    <div class="modal fade bs-modal-lg" tabindex="-1" role="basic" aria-hidden="true" id="tickets-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-warning bold">
                        <i class="fa fa-support"></i>
                        Заявки с номера заявителя
                    </h4>
                </div>
                <div class="modal-body" id="customer_tickets">

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

    <div class="modal fade bs-modal-lg" tabindex="-1" role="basic" aria-hidden="true" id="works-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title bold text-danger">
                        <i class="fa fa-wrench"></i>
                        Работы на сетях
                    </h4>
                </div>
                <div class="modal-body" id="works">

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
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
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
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-form-tools.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js" type="text/javascript"></script>
    <script src="https://webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();
        var timers = {};

        function GetTickets ()
        {

            var phone = $.trim( $( '#phone' ).val() );

            if ( ! phone || /_/.test( phone ) )
            {
                return;
            }

            $.get( '{{ route( 'tickets.search' ) }}', {
                phone: phone,
                region_id: $( '#region_id' ).val()
            }, function ( response )
            {
                if ( response )
                {
                    $( '#customer_tickets' ).html( response );
                    $( '#tickets-modal' ).modal( 'show' );
                }
                else
                {
                    $( '#customer_tickets' ).empty();
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
                $( '#info-block' ).removeClass( 'hidden' );
                $( '#period_acceptance' ).text( response.period_acceptance + ' ч.' );
                $( '#period_execution' ).text( response.period_execution + ' ч.' );
                $( '#season' ).text( response.season );
                $( '#category' ).text( response.category_name );
                if ( response.emergency )
                {
                    $( '#emergency' ).prop( 'checked', 'checked' ).attr( 'disabled', 'disabled' );
                }
                else
                {
                    $( '#emergency' ).removeAttr( 'checked' ).removeAttr( 'disabled' );
                }
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

        function GetWorks ()
        {

            var address_id = $( '#address_id' ).val();
            if ( ! address_id )
            {
                return;
            };
            $.get( '{{ route( 'works.search' ) }}', {
                address_id: address_id
            }, function ( response )
            {
                if ( response )
                {
                    $( '#works' ).html( response );
                    $( '#works-modal' ).modal( 'show' );
                }
                else
                {
                    $( '#works' ).empty();
                }
            });

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
                        $.getJSON( '{{ route( 'customers.search' ) }}', r, function ( data )
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
                    var id = $( '#ticket_id' ).val();
                    var tag = e.item;
                    if ( ! id || ! tag ) return;
                    $.post( '{{ route( 'tickets.add-tag' ) }}', {
                        id: id,
                        tag: tag
                    });
                });

                $( '#tags' ).on( 'itemRemoved', function ( e )
                {
                    var id = $( '#ticket_id' ).val();
                    var tag = e.item;
                    if ( ! id || ! tag ) return;
                    $.post( '{{ route( 'tickets.del-tag' ) }}', {
                        id: id,
                        tag: tag
                    });
                });

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

                $( '#address_id, #actual_address_id' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        data: function ( term, page )
                        {
                            return {
                                q: term.term,
                                region_id: $( '#region_id' ).val()
                            };
                        },
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

                GetTypeInfo();
                GetManagements();
                GetWorks();

                $( '#phone' ).trigger( 'change' );

            })

            .on( 'change', '#region_id', function ( e )
            {
                $( '#address_id' ).val( '' ).trigger( 'change' );
            })

            .on( 'change', '.autosave', function ( e )
            {
                var id = $( '#ticket_id' ).val();
                if ( ! id ) return;
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
                    $.post( '{{ route( 'tickets.save' ) }}', {
                        id: id,
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

            .on( 'change', '#address_id, #type_id', function ( e )
            {
                GetManagements();
            })

            .on( 'change', '#address_id', function ( e )
            {
                GetWorks();
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
                        $.getJSON( '{{ route( 'customers.search' ) }}', r, function ( response )
                        {
                            if ( ! response ) return;
                            var phone = $.trim( $( '#phone' ).val().replace( '/\D/', '' ) );
                            var actual_address_id = $( '#actual_address_id' ).val();
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
                            if ( ! actual_address_id && response.actual_address_id )
                            {
                                $( '#actual_address_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_address_id )
                                            .text( response.actual_address.name )
                                    )
                                    .val( response.actual_address_id )
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
                GetTickets();
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
                    var actual_address_id = $( '#actual_address_id' ).val();
                    var actual_flat = $( '#actual_flat' ).val();
                    if ( ! firstname || ! middlename || ! lastname || ! actual_address_id || ! actual_flat )
                    {
                        var r = {};
                        r.param = param;
                        r.phone = phone;
                        $.getJSON( '{{ route( 'customers.search' ) }}', r, function ( response )
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
                            if ( ! actual_address_id && response.actual_address_id )
                            {
                                $( '#actual_address_id' )
                                    .append(
                                        $( '<option>' )
                                            .val( response.actual_address_id )
                                            .text( response.actual_address.name )
                                    )
                                    .val( response.actual_address_id )
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