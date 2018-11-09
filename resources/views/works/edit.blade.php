@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ 'Отключения', route( 'works.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $work, [ 'method' => 'put', 'route' => [ 'works.update', $work->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="row">

        <div class="col-lg-6">

            @if ( $providers->count() > 1 )
                <div class="form-group">
                    {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $works->provider_id ?? null ), [ 'class' => 'form-control select2 autosave', 'data-placeholder' => ' -- выберите из списка -- ', 'required', 'autocomplete' => 'off' ] ) !!}
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label( 'type_id', 'Тип', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'type_id', \App\Models\Work::$types, \Input::old( 'type_id', $work->type_id ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
                </div>
            </div>

                <div class="form-group">
                    {!! Form::label( 'managements[]', 'Исполнитель работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::select( 'managements[]', $availableManagements, \Input::old( 'managements', $work->managements->pluck( 'id' ) ), [ 'class' => 'form-control select2', 'required', 'multiple', 'id' => 'managements' ] ) !!}
                    </div>
                </div>

                <div class="form-group @if ( ! count( \Input::old( 'managements', $work->managements ) ) ) hidden @endif" id="executor">
                    {!! Form::label( 'executors[]', 'Ответственный', [ 'class' => 'control-label col-xs-3' ] ) !!}
                    <div class="col-xs-9">
                        {!! Form::select( 'executors[]', [], \Input::old( 'executors', $work->executors->pluck( 'id' ) ), [ 'class' => 'form-control select2', 'multiple', 'id' => 'executors' ] ) !!}
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
                    {!! Form::text( 'date_begin', \Input::old( 'date_begin', \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата начала работ', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_begin', \Input::old( 'time_begin', \Carbon\Carbon::parse( $work->time_begin )->format( 'H:i' ) ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время начала работ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'date_end', 'Дата окончания работ (план.)', [ 'class' => 'control-label col-xs-4' ] ) !!}
                <div class="col-xs-4">
                    {!! Form::text( 'date_end', \Input::old( 'date_end', \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата окончания работ (план.)', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_end', \Input::old( 'time_end', \Carbon\Carbon::parse( $work->time_end )->format( 'H:i' ) ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время окончания работ (план.)', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'deadline', 'Предельное время устранения', [ 'class' => 'control-label col-xs-6' ] ) !!}
                <div class="col-xs-3">
                    {!! Form::number( 'deadline', \Input::old( 'deadline', $work->deadline ), [ 'class' => 'form-control', 'min' => 0, 'step' => 1 ] ) !!}
                </div>
                <div class="col-xs-3">
                    {!! Form::select( 'deadline_unit', \App\Models\Work::$deadline_units, \Input::old( 'deadline_unit', $work->deadline_unit ), [ 'class' => 'form-control' ] ) !!}
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-group">
                {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'category_id', $availableCategories, \Input::old( 'category_id', $work->category_id ), [ 'class' => 'form-control select2', 'data-placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'buildings[]', 'Адрес работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default tooltips" title="Очистить адреса" data-empty="#buildings">
                                -
                            </button>
                        </span>
                        {!! Form::select( 'buildings[]', $work->buildings()->pluck( \App\Models\Building::$_table . '.name', \App\Models\Building::$_table . '.id' ), $work->buildings()->pluck( \App\Models\Building::$_table . '.id' ), [ 'class' => 'form-control', 'id' => 'buildings', 'data-placeholder' => 'Адрес работы', 'required', 'multiple' ] ) !!}
                        <span class="input-group-btn">
                            <button class="btn btn-default tooltips" title="Добавить адреса" data-group-buildings="#buildings">
                                +
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'composition', 'Состав работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::textarea( 'composition', \Input::old( 'composition', $work->composition ), [ 'class' => 'form-control', 'placeholder' => 'Состав работ', 'required', 'rows' => 8 ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'reason', 'Основание', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'reason', \Input::old( 'reason', $work->reason ), [ 'class' => 'form-control', 'placeholder' => 'Основание' ] ) !!}
                </div>
            </div>

        </div>

    </div>

    <div class="row margin-top-10">
        <div class="col-xs-6">
            <button type="button" class="btn btn-danger btn-lg" id="work-close">
                <i class="fa fa-close"></i>
                Завершить
            </button>
        </div>
        <div class="col-xs-6 text-right">
            {!! Form::hidden( 'closed', '0', [ 'id' => 'closed' ] ) !!}
            <button type="submit" class="btn green btn-lg">
                <i class="fa fa-plus"></i>
                Сохранить
            </button>
        </div>
    </div>

    @if ( \Auth::user()->can( 'works.comments' ) )
        <div class="row margin-top-10">
            <div class="col-lg-12">
                <div class="note">
                    @if ( $work->canComment() )
                        <button type="button" class="btn blue btn-lg pull-right" data-action="comment" data-model-name="{{ get_class( $work ) }}" data-model-id="{{ $work->id }}" data-origin-model-name="{{ get_class( $work ) }}" data-origin-model-id="{{ $work->id }}" data-file="1">
                            <i class="fa fa-comment"></i>
                            Добавить комментарий
                        </button>
                    @endif
                    <h4>Комментарии</h4>
                    @if ( $work->comments->count() )
                        @include( 'parts.comments', [ 'origin' => $work, 'comments' => $work->comments ] )
                    @endif
                </div>
            </div>
        </div>
    @endif

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
    <script type="text/javascript">

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

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.datepicker' ).datepicker();

                $( '.timepicker-24' ).timepicker({
                    autoclose: true,
                    minuteStep: 5,
                    showSeconds: false,
                    showMeridian: false,
                    defaultTime: false
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

                getExecutors( '{{ $work->executors->implode( 'id', ',' ) }}'.split( ',' ) );

            })

            .on( 'click', '.executor-toggle', function ( e )
            {
                $( '#executor_name, #executor_phone' ).val( '' );
            })

            .on( 'click', '#work-close', function ( e )
            {
                if ( ! confirm( 'Вы уверены?' ) ) return;
                $( '#closed' ).val( '1' );
                $( this ).closest( 'form' ).submit();
            })

            .on( 'change', '#managements', getExecutors )

            .on( 'change', '#provider_id, #managements', function ( e )
            {
                $( '#buildings' ).val( '' ).trigger( 'change' );
            });

    </script>
@endsection