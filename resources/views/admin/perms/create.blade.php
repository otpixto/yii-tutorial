@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права', route( 'perms.index' ) ],
        [ 'Создать права' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'perms.store' ) ] ) !!}

                <div class="form-group">
                    <label class="control-label">Код</label>
                    {!! Form::text( 'code', \Input::old( 'code' ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Guard</label>
                    {!! Form::select( 'guard_name', $guards, \Input::old( 'guard_name', config( 'defaults.guard' ) ), [ 'class' => 'form-control' ] ) !!}
                </div>

                <div class="caption caption-md">
                    <i class="icon-globe theme-font hide"></i>
                    <span class="caption-subject font-blue-madison bold uppercase">Применить для ролей</span>
                </div>

                <div class="mt-checkbox-list">
                    @foreach ( $roles as $role )
                        <label class="mt-checkbox mt-checkbox-outline">
                            {{ $role->name }}
                            {!! Form::checkbox( 'roles[]', $role->code ) !!}
                            <span></span>
                        </label>
                    @endforeach
                </div>

                <div class="margin-top-10">
                    {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
@endsection