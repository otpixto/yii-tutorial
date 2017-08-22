@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">

        <div class="col-xs-6">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $type->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="col-xs-6">
            {!! Form::label( 'category_id', 'Категория обращений', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'category_id', $categories, \Input::old( 'category_id', $type->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория обращений' ] ) !!}
        </div>

    </div>

    <div class="form-group">

        <div class="col-xs-6">
            {!! Form::label( 'period_acceptance', 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
            {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance', $type->period_acceptance ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
        </div>

        <div class="col-xs-6">
            {!! Form::label( 'period_execution', 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
            {!! Form::number( 'period_execution', \Input::old( 'period_execution', $type->period_execution ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
        </div>

    </div>

    <div class="form-group">

        <div class="col-xs-8">
            {!! Form::label( 'season', 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'season', \Input::old( 'season', $type->season ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
        </div>

        <div class="col-xs-4">
            {!! Form::label( 'need_act', 'Необходим акт', [ 'class' => 'control-label' ] ) !!}
            <br />{!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $type->need_act ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
        </div>

    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.select2' ).select2();

            });

    </script>
@endsection