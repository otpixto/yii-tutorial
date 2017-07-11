@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права', route( 'perms.index' ) ],
        [ 'Редактировать права "' . $perm->name . '"' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::model( $perm, [ 'method' => 'put', 'route' => [ 'perms.update', $perm->id ] ] ) !!}

                <div class="form-group">
                    <label class="control-label">Код</label>
                    {!! Form::text( 'code', \Input::old( 'code', $perm->code ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name', $perm->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Guard</label>
                    {!! Form::select( 'guard_name', $guards, \Input::old( 'guard_name', $perm->guard_name ), [ 'class' => 'form-control' ] ) !!}
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