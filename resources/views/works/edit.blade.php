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
                        {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $draft->provider_id ?? null ), [ 'class' => 'form-control select2 autosave', 'data-placeholder' => ' -- выберите из списка -- ', 'required', 'autocomplete' => 'off' ] ) !!}
                    </div>
                </div>
            @endif

            <div class="form-group">
                {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'category_id', [ null => ' -- выберите из списка -- ' ] + $categories->toArray(), \Input::old( 'category_id', $work->category_id ), [ 'class' => 'form-control select2', 'data-placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'buildings[]', 'Адрес работы', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'buildings[]', $work->buildings()->pluck( \App\Models\Building::$_table . '.name', \App\Models\Building::$_table . '.id' ), $work->buildings()->pluck( \App\Models\Building::$_table . '.id' ), [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес работы', 'required', 'multiple' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'date_begin', 'Дата и время начала работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::text( 'date_begin', \Input::old( 'date_begin', \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата начала работ', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_begin', \Input::old( 'time_begin', \Carbon\Carbon::parse( $work->time_begin )->format( 'H:i' ) ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время начала работ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'date_end', 'Дата окончания работ (план.)', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::text( 'date_end', \Input::old( 'date_end', \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата окончания работ (план.)', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_end', \Input::old( 'time_end', \Carbon\Carbon::parse( $work->time_end )->format( 'H:i' ) ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время окончания работ (план.)', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'date_end_fact', 'Дата окончания работ (факт.)', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::text( 'date_end_fact', \Input::old( 'date_end_fact', $work->time_end_fact ? \Carbon\Carbon::parse( $work->time_end_fact )->format( 'd.m.Y' ) : null ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата окончания работ (факт.)' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_end_fact', \Input::old( 'time_end_fact', $work->time_end_fact ? \Carbon\Carbon::parse( $work->time_end_fact )->format( 'H:i' ) : null ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время окончания работ (факт.)' ] ) !!}
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-group">
                {!! Form::label( 'management_id', 'Исполнитель работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'management_id', [ null => ' -- выберите из списка -- ' ] + $managements->pluck( 'name', 'id' )->toArray(), \Input::old( 'management_id', $work->management_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Исполнитель работ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group @if ( ! $work->management ) hidden @endif" id="executor">
                {!! Form::label( 'executor_id', 'Ответственный', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-7">
                    <select name="executor_id" class="form-control select2">
                        <option value=""> -- выберите из списка -- </option>
                        @foreach ( $work->management->executors as $executor )
                            <option value="{{ $executor->id }}" @if ( $executor->id == $work->executor_id ) selected="selected" @endif>{{ $executor->getName( true ) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xs-2">
                    <button type="button" class="btn btn-primary executor-toggle" data-toggle="#executor_create, #executor">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="hidden" id="executor_create">

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

            </div>

            <div class="form-group">
                {!! Form::label( 'composition', 'Состав работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'composition', \Input::old( 'composition', $work->composition ), [ 'class' => 'form-control', 'placeholder' => 'Состав работ', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'reason', 'Основание', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'reason', \Input::old( 'reason', $work->reason ), [ 'class' => 'form-control', 'placeholder' => 'Основание', 'required' ] ) !!}
                </div>
            </div>

        </div>

    </div>

    <div class="row margin-top-10">
        <div class="col-lg-6 margin-bottom-10">
            <button type="submit" class="btn green btn-block btn-lg">
                <i class="fa fa-edit"></i>
                Сохранить
            </button>
        </div>
        @if ( \Auth::user()->can( 'works.comments' ) )
            <div class="col-lg-6">
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
        @endif
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
    <script type="text/javascript">

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

            })

            .on( 'click', '.executor-toggle', function ( e )
            {
                $( '#executor_name, #executor_phone' ).val( '' );
            })

            .on( 'change', '#management_id', function ( e )
            {
                var management_id = $( this ).val();
                $( '#executor_id' ).empty();
                if ( management_id )
                {
                    $( '#executor' ).removeClass( 'hidden' );
                    $.get( '{{ route( 'managements.executors.search' ) }}', {
                        management_id: management_id
                    }, function ( response )
                    {
                        $.each( response, function ( i, executor )
                        {
                            $( '#executor_id' ).append(
                                $( '<option>' ).val( executor.id ).text( executor.name )
                            );
                        });
                    });
                }
                else
                {
                    $( '#executor' ).addClass( 'hidden' );
                }
            })

            .on( 'change', '#provider_id', function ( e )
            {
                $( '#buildings' ).val( '' ).trigger( 'change' );
            });

    </script>
@endsection