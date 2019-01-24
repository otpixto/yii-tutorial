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

            <div class="form-group">
                {!! Form::label( null, 'Автор', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->author->getName() }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Тип', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ \App\Models\Work::$types[ $work->type_id ] }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Исполнитель работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->managements->implode( 'name' ) }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Ответственный', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->executors->implode( 'name' ) }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Дата и время начала работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y H:i' ) }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Дата окончания работ (план.)', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y H:i' ) }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Дата окончания работ (факт.)', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->time_end_fact ? \Carbon\Carbon::parse( $work->time_end_fact )->format( 'd.m.Y H:i' ) : '-' }}
                    </span>
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-group">
                {!! Form::label( null, 'Категория', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->category->name }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Адрес(а) работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        @foreach ( $work->getAddressesGroupBySegment() as $segment )
                            <div class="margin-top-5">
                                <span class="small">
                                    {{ $segment[ 0 ] }}
                                </span>
                                @if ( ! empty( $segment[ 1 ] ) )
                                    <span class="bold">
                                        д. {{ implode( ', ', $segment[ 1 ] ) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Состав работ', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->composition }}
                    </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label( null, 'Основание', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    <span class="form-control-static">
                        {{ $work->reason }}
                    </span>
                </div>
            </div>

        </div>

    </div>

    {!! Form::close() !!}

    @if ( \Auth::user()->can( 'works.comments' ) )
        <div class="row margin-top-10">
            <div class="col-xs-12">
                <div class="note">
                    <h4>Комментарии</h4>
                    @if ( $work->comments->count() )
                        @include( 'parts.comments', [ 'origin' => $work, 'comments' => $work->comments ] )
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ( $work->canComment() )
        <div class="row margin-top-10">
            <div class="col-xs-12">
                <button type="button" class="btn blue btn-lg" data-action="comment" data-model-name="{{ get_class( $work ) }}" data-model-id="{{ $work->id }}" data-origin-model-name="{{ get_class( $work ) }}" data-origin-model-id="{{ $work->id }}" data-file="1">
                    <i class="fa fa-comment"></i>
                    Добавить комментарий
                </button>
            </div>
        </div>
    @endif

    {!! Form::close() !!}

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
@endsection