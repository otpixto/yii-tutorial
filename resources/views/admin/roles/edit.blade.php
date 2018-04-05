@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.edit' ) )

        {!! Form::model( $role, [ 'method' => 'put', 'route' => [ 'roles.update', $role->id ], 'id' => 'role-edit-form' ] ) !!}

        <div class="form-group">
            {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'code', \Input::old( 'code', $role->code ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $role->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'guard', $guards, \Input::old( 'guard', $role->guard ), [ 'class' => 'form-control' ] ) !!}
        </div>

        <div class="margin-top-10">
            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            <a href="{{ route( 'roles.perms', $role->id ) }}" class="btn btn-warning">
                Права доступа
            </a>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection