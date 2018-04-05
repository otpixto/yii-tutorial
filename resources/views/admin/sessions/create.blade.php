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

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.create' ) )

        {!! Form::open( [ 'url' => route( 'sessions.store' ) ] ) !!}

        <div class="form-group">
            {!! Form::label( 'user_id', 'Пользователь', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'user_id', [ 0 => ' -- выберите из списка -- ' ] + $operators, \Input::old( 'user_id' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Пользователь' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'number', 'Номер телефона', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'number', \Input::old( 'number' ), [ 'class' => 'form-control', 'placeholder' => 'Номер телефона', 'maxlength' => 10 ] ) !!}
        </div>

        <div class="margin-top-10">
            {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.select2' ).select2();

            });

    </script>

@endsection