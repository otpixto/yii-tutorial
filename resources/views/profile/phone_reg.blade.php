@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'class' => 'form-horizontal submit-loading' ] ) !!}

    @if ( $providers->count() > 1 )
        <div class="form-group">
            {!! Form::label( 'provider_id', 'Провайдер', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-6">
                {!! Form::select( 'provider_id', $providers->pluck( 'name', 'id' ), \Input::old( 'provider_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>
        </div>
    @endif

    <div class="form-group">
        {!! Form::label( 'number', 'Номер', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::text( 'number', \Input::old( 'number' ), [ 'class' => 'form-control', 'maxlength' => 10, 'autofocus', 'required' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class=" col-xs-offset-3 col-xs-6">
            {!! Form::submit( 'Авторизоваться', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

@endsection