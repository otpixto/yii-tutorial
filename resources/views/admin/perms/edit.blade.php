@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права', route( 'perms.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $perm, [ 'method' => 'put', 'route' => [ 'perms.update', $perm->id ] ] ) !!}

    <div class="form-group">
        {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
        {!! Form::text( 'code', \Input::old( 'code', $perm->code ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
        {!! Form::text( 'name', \Input::old( 'name', $perm->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::label( 'guard_name', 'Guard', [ 'class' => 'control-label' ] ) !!}
        {!! Form::select( 'guard_name', $guards, \Input::old( 'guard_name', $perm->guard_name ), [ 'class' => 'form-control' ] ) !!}
    </div>

    <div class="margin-top-10">
        {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
    </div>

    {!! Form::close() !!}

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
@endsection