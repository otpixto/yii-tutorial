@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        {!! Form::model( $role, [ 'method' => 'put', 'route' => [ 'roles.update', $role->id ], 'id' => 'role-edit-form', 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'code', \Input::old( 'code', $role->code ), [ 'class' => 'form-control', 'placeholder' => 'Код', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name', $role->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'guard', $guards, \Input::old( 'guard', $role->guard ), [ 'class' => 'form-control', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'roles.perms', $role->id ) }}" class="btn btn-default btn-circle">
                    Права доступа
                    <span class="badge">{{ $role->permissions->count() }}</span>
                </a>
            </div>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection