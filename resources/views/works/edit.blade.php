@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Работы на сетях', route( 'works.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $work, [ 'method' => 'put', 'route' => [ 'works.update', $work->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="row">

        <div class="col-lg-6">

            <div class="form-group">
                {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'region_id', $regions, \Input::old( 'region_id', $draft->region_id ?? null ), [ 'class' => 'form-control select2 autosave', 'placeholder' => 'Регион', 'data-placeholder' => 'Регион', 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'address_id', 'Адрес работы', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'address_id[]', $work->addresses->pluck( 'name', 'id' ), $work->addresses->pluck( 'id' ), [ 'class' => 'form-control', 'id' => 'address_id', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес работы', 'data-allow-clear' => true, 'required', 'multiple' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'category_id', [ null => ' -- выберите из списка -- ' ] + \App\Models\Work::$categories, \Input::old( 'category_id', $work->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория', 'required' ] ) !!}
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
                {!! Form::label( 'date_end', 'Дата окончания работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-5">
                    {!! Form::text( 'date_end', \Input::old( 'date_end', \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => 'Дата окончания работ', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::text( 'time_end', \Input::old( 'time_end', \Carbon\Carbon::parse( $work->time_end )->format( 'H:i' ) ), [ 'class' => 'form-control timepicker timepicker-24', 'placeholder' => 'Время окончания работ', 'required' ] ) !!}
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

            <div class="form-group">
                {!! Form::label( 'who', 'Кто передал', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'who', \Input::old( 'who', $work->who ), [ 'class' => 'form-control', 'placeholder' => 'Должность и ФИО', 'required' ] ) !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( 'phone', 'Контактный телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::text( 'phone', \Input::old( 'phone', $work->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Контактный телефон' ] ) !!}
                </div>
            </div>

        </div>

    </div>

    <div class="form-group">
        <div class="col-xs-4">
            <button type="submit" class="btn green btn-block btn-lg">
                <i class="fa fa-edit"></i>
                Сохранить
            </button>
        </div>
    </div>

    {!! Form::close() !!}

    <div class="row margin-top-10">
        <div class="col-xs-12">
            <div class="note">
                <h4>Комментарии</h4>
                @if ( $work->comments->count() )
                    @include( 'parts.comments', [ 'comments' => $work->comments ] )
                @endif
            </div>
        </div>
    </div>

    <div class="row margin-top-10">
        <div class="col-xs-12">
            <button type="button" class="btn blue btn-lg" data-action="comment" data-model-name="{{ get_class( $work ) }}" data-model-id="{{ $work->id }}" data-origin-model-name="{{ get_class( $work ) }}" data-origin-model-id="{{ $work->id }}" data-file="1">
                <i class="fa fa-comment"></i>
                Добавить комментарий
            </button>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
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
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
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

                $( '#address_id' ).select2({
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

                $( '.datepicker' ).datepicker();

                $( '.timepicker-24' ).timepicker({
                    autoclose: true,
                    minuteStep: 5,
                    showSeconds: false,
                    showMeridian: false
                });


            })

            .on( 'change', '#region_id', function ( e )
            {
                $( '#address_id' ).val( '' ).trigger( 'change' );
            });

    </script>
@endsection