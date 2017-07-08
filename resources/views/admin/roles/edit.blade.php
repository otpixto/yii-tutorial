@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ 'Редактировать роль "' . $role->name . '"' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::model( $role, [ 'method' => 'put', 'route' => [ 'roles.update', $role->id ] ] ) !!}

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name', $role->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="caption caption-md">
                    <i class="icon-globe theme-font hide"></i>
                    <span class="caption-subject font-blue-madison bold uppercase">Права доступа</span>
                </div>

                <div class="mt-checkbox-list">
                    @foreach ( $perms as $perm )
                        <label class="mt-checkbox mt-checkbox-outline">
                            {{ $perm->name }}
                            {!! Form::checkbox( 'perms[]', $perm->name, $role->hasPermissionTo( $perm->name ) ) !!}
                            <span></span>
                        </label>
                    @endforeach
                </div>

                <div class="margin-top-10">
                    {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
@endsection