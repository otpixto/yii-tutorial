@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Авторизация на телефоне', route( 'sessions.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'sessions.store' ) ] ) !!}

    <div class="form-group">
        {!! Form::label( 'user_id', 'Пользователь', [ 'class' => 'control-label' ] ) !!}
        {!! Form::select( 'user_id', $users, \Input::old( 'user_id' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Пользователь' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::label( 'number', 'Номер телефона', [ 'class' => 'control-label' ] ) !!}
        {!! Form::text( 'number', \Input::old( 'number' ), [ 'class' => 'form-control', 'placeholder' => 'Номер телефона' ] ) !!}
    </div>

    <div class="margin-top-10">
        {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
    </div>

    {!! Form::close() !!}

@endsection