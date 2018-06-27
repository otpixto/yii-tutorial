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

    @if ( \Auth::user()->can( 'catalog.types.create' ) )

        {!! Form::open( [ 'url' => route( 'types.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-xs-6">
                {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'category_id', $categories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
            </div>

            <div class="col-xs-6">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-md-3">
                {!! Form::label( 'period_acceptance', 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
                {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance' ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
            </div>

            <div class="col-md-3">
                {!! Form::label( 'period_execution', 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
                {!! Form::number( 'period_execution', \Input::old( 'period_execution' ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
            </div>
            <div class="col-md-6">
                {!! Form::label( 'season', 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'season', \Input::old( 'season' ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'need_act', 'Необходим акт', [ 'class' => 'control-label' ] ) !!}
                <br />{!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act' ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'is_pay', 'Платно', [ 'class' => 'control-label' ] ) !!}
                {!! Form::checkbox( 'is_pay', 1, \Input::old( 'is_pay' ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'emergency', 'Авария', [ 'class' => 'control-label' ] ) !!}
                {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency' ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-12">
                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'guid', \Input::old( 'guid' ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
            </div>

        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection