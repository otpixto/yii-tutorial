@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">
        {!! Form::label( 'ext_number', 'Добавочный номер', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::text( 'ext_number', \Input::old( 'ext_number' ), [ 'class' => 'form-control', 'maxlength' => 4, 'autofocus' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class=" col-xs-offset-3 col-xs-6">
            {!! Form::submit( 'Отправить код', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

@endsection