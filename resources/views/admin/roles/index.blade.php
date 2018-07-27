@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'roles.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Создать роль
                </a>
            </div>
        </div>

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'roles.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                    </div>
                    <div class="portlet-body todo-project-list-content" style="height: auto;">
                        <div class="todo-project-list">
                            {!! Form::open( [ 'method' => 'get' ] ) !!}
                            {!! Form::hidden( 'guard', $guard ) !!}
                            <div class="row">
                                <div class="col-xs-12">
                                    {!! Form::text( 'search', $search ?? null, [ 'class' => 'form-control' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-xs-12">
                                    {!! Form::submit( 'Найти', [ 'class' => 'btn btn-info btn-block' ] ) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TODO SIDEBAR -->

            <!-- BEGIN TODO CONTENT -->
            <div class="todo-content">
                <div class="portlet light ">
                    <div class="portlet-body">

                        <ul class="nav nav-tabs">
                            @foreach ( $guards as $_guard )
                                <li role="presentation" @if ( $_guard == $guard ) class="active" @endif>
                                    <a href="?guard={{ $_guard }}">
                                        {{ $_guard }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if ( $roles->count() )

                            {{ $roles->render() }}

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>
                                        Наименование
                                    </th>
                                    <th>
                                        Код
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $roles as $role )
                                    <tr>
                                        <td>
                                            {{ $role->name }}
                                        </td>
                                        <td>
                                            {{ $role->code }}
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.edit' ) )
                                                <a href="{{ route( 'roles.edit', $role->id ) }}" class="btn btn-info tooltips" title="Редактировать">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route( 'roles.perms', $role->id ) }}" class="btn btn-warning tooltips" title="Права доступа">
                                                    <i class="fa fa-unlock-alt"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $roles->render() }}

                        @else
                            @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                        @endif

                    </div>
                </div>
            </div>
            <!-- END TODO CONTENT -->
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection