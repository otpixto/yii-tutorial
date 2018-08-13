@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ 'Отключения', route( 'works.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'works.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="row">

        <div class="col-lg-6">

            @if ( $providers->count() > 1 )
                <div class="form-group">
                    {!! Form::label( 'Поставщик', 'Поставщик', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $draft->provider_id ?? null ), [ 'class' => 'form-control select2 autosave', 'data-placeholder' => ' -- выберите из списка -- ', 'required', 'autocomplete' => 'off' ] ) !!}
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label( 'type_id', 'Тип', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'type_id', \App\Models\Work::$types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'managements[]', 'Исполнитель работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'managements[]', $availableManagements, \Input::old( 'managements' ), [ 'class' => 'form-control select2', 'required', 'multiple', 'id' => 'managements' ] ) !!}
                </div>
            </div>

                <div class="form-group @if ( ! count( \Input::old( 'managements', [] ) ) ) hidden @endif" id="executor">
                {!! Form::label( 'executors[]', 'Ответственный', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'executors[]', [], \Input::old( 'executors' ), [ 'class' => 'form-control select2', 'multiple', 'id' => 'executors' ] ) !!}
                </div>
                {{--<div class="col-xs-2">
                    <button type="button" class="btn btn-primary executor-toggle" data-toggle="#executor_create, #executor">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>--}}
            </div>

            {{--<div class="hidden" id="executor_create">

                <div class="form-group">
                    {!! Form::label( 'executor_name', 'Ответственный', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-7">
                        {!! Form::text( 'executor_name', \Input::old( 'executor_name' ), [ 'class' => 'form-control', 'placeholder' => 'Должность и ФИО' ] ) !!}
                    </div>
                    <div class="col-xs-2">
                        <button type="button" class="btn btn-danger executor-toggle" data-toggle="#executor_create, #executor">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label( 'executor_phone', 'Контактный телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::text( 'executor_phone', \Input::old( 'executor_phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Контактный телефон' ] ) !!}
                    </div>
                </div>

            </div>--}}

            <div class="form-group">
                {!! Form::label( 'date_begin', 'Дата и время начала работ', [ 'class' => 'control-label col-xs-4' ] ) !!}
                <div class="col-xs-4">
                    {!! Form::text( 'date_begin', \Input::old( 'date_begin', date( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата начала работ', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_begin', \Input::old( 'time_begin' ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время начала работ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'date_end', 'Дата окончания работ (план.)', [ 'class' => 'control-label col-xs-4' ] ) !!}
                <div class="col-xs-4">
                    {!! Form::text( 'date_end', \Input::old( 'date_end', date( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата окончания работ (план.)', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_end', \Input::old( 'time_end' ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время окончания работ (план.)', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'deadline', 'Предельное время устранения', [ 'class' => 'control-label col-xs-6' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::number( 'deadline', \Input::old( 'deadline' ), [ 'class' => 'form-control', 'min' => 0, 'step' => 1 ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::select( 'deadline_unit', \App\Models\Work::$deadline_units, \Input::old( 'deadline_unit' ), [ 'class' => 'form-control' ] ) !!}
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-group">
                {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'category_id', $availableCategories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'buildings', 'Адрес работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'buildings[]', $buildings->pluck( 'name', 'id' )->toArray(), $buildings->pluck( 'id' ), [ 'class' => 'form-control', 'id' => 'buildings', 'data-placeholder' => 'Адрес работ', 'required', 'multiple' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'composition', 'Состав работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::textarea( 'composition', \Input::old( 'composition' ), [ 'class' => 'form-control', 'placeholder' => 'Состав работ', 'required', 'rows' => 6 ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'reason', 'Основание', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'reason', \Input::old( 'reason' ), [ 'class' => 'form-control', 'placeholder' => 'Основание' ] ) !!}
                </div>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-xs-12">

            <button type="button" class="btn btn-default margin-bottom-5" id="microphone" data-state="off">
                <i class="fa fa-microphone-slash"></i>
            </button>
            {!! Form::label( 'comment', 'Комментарий', [ 'class' => 'control-label' ] ) !!}
            {!! Form::textarea( 'comment', \Input::old( 'comment' ), [ 'class' => 'form-control autosizeme', 'placeholder' => 'Комментарий', 'rows' => 3 ] ) !!}

        </div>

    </div>

    <div class="row margin-top-10">

        <div class="col-xs-offset-6 col-xs-6">
            <button type="submit" class="btn green btn-block btn-lg">
                <i class="fa fa-plus"></i>
                Добавить
            </button>
        </div>

    </div>

    {!! Form::close() !!}

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <style>
        .mt-checkbox, .mt-radio {
            margin-bottom: 0;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-form-tools.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/autosize/autosize.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="//webasr.yandex.net/jsapi/v1/webspeechkit.js" type="text/javascript"></script>
    <script type="text/javascript">

        var streamer = new ya.speechkit.SpeechRecognition();

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
                        var value = $.trim( $( '#comment' ).val() );
                        if ( value != '' )
                        {
                            value += "\n";
                        }
                        $( '#comment' ).val( value + text );
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

        function getExecutors ( selected )
        {
            var managements = $( '#managements' ).val();
            $( '#executors' ).empty();
            if ( managements.length )
            {
                $( '#executor' ).removeClass( 'hidden' );
                $.get( '{{ route( 'managements.executors.search' ) }}', {
                    managements: managements
                }, function ( response )
                {
                    $.each( response, function ( i, executor )
                    {
                        $( '#executors' ).append(
                            $( '<option>' ).val( executor.id ).text( executor.name )
                        );
                    });
                    if ( selected )
                    {
                        $( '#executors' ).val( selected ).trigger( 'change' );
                    }
                });
            }
            else
            {
                $( '#executor' ).addClass( 'hidden' );
            }
        };

        $( document )

            .ready( function ()
            {

                $( '#microphone' ).click( ToggleMicrophone );

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.datepicker' ).datepicker();

                $( '.timepicker-24' ).timepicker({
                    autoclose: true,
                    minuteStep: 5,
                    showSeconds: false,
                    showMeridian: false
                });

                $( '#buildings' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        url: '{{ route( 'works.buildings.search' ) }}',
                        cache: true,
                        type: 'post',
                        delay: 450,
                        data: function ( term )
                        {
                            var data = {
                                q: term.term,
                                provider_id: $( '#provider_id' ).val(),
                                category_id: $( '#category_id' ).val(),
                                managements: $( '#managements' ).val()
                            };
                            return data;
                        },
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

                getExecutors( '{{ implode( ',', \Input::old( 'executors', [] ) ) }}'.split( ',' ) );

            })

            .on( 'keydown', function ( e )
            {
                if ( e.ctrlKey && e.which == 32 )
                {
                    ToggleMicrophone();
                }
            })

            .on( 'click', '.executor-toggle', function ( e )
            {
                $( '#executor_name, #executor_phone' ).val( '' );
            })

            .on( 'change', '#managements', getExecutors )

            .on( 'change', '#provider_id, #managements, #category_id', function ( e )
            {
                $( '#buildings' ).val( '' ).trigger( 'change' );
            });

    </script>
@endsection