@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'users.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Создать пользователя
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
                    <a href="{{ route( 'users.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
                    <div class="todo-project-list">
                        {!! Form::open( [ 'method' => 'get' ] ) !!}
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
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                        <span class="caption-subject font-green-sharp bold uppercase">РОЛИ</span>
                        <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                    </div>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
                    <div class="todo-project-list">
                        <ul class="nav nav-stacked">
                            @foreach ( $roles as $role )
                                <li @if ( \Input::get( 'role' ) == $role->code ) class="active" @endif>
                                    <a href="?role={{ $role->code }}">
                                        {{ $role->name }}
                                        <span class="badge badge-info pull-right">
                                            {{ $role->users->count() }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                        <span class="caption-subject font-green-sharp bold uppercase">Регионы</span>
                        <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                    </div>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
                    <div class="todo-project-list">
                        <ul class="nav nav-stacked">
                            @foreach ( $regions as $region )
                                <li @if ( \Input::get( 'region' ) == $region->id ) class="active" @endif>
                                    <a href="?region={{ $region->id }}">
                                        {{ $region->name }}
                                        <span class="badge badge-info pull-right">
                                            {{ $region->users->count() }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TODO SIDEBAR -->

        <!-- BEGIN TODO CONTENT -->
        <div class="todo-content">
            <div class="portlet light ">
                <div class="portlet-body">

                    @if ( $users->count() )

                        {{ $users->render() }}

                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>
                                    E-mail
                                </th>
                                <th>
                                    ФИО
                                </th>
                                <th>
                                    Телефон
                                </th>
                                <th>
                                    Регионы
                                </th>
                                <th>
                                    Роли
                                </th>
                                <th>
                                    Активен
                                </th>
                                <th class="text-right">
                                    &nbsp;
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $users as $user )
                                <tr class="{{ $user->admin ? 'text-danger bold' : '' }}">
                                    <td>
                                        {{ $user->email }}
                                    </td>
                                    <td>
                                        <span class="small">
                                            {{ $user->getName() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small">
                                            {{ $user->getPhone() }}
                                        </span>
                                    </td>
                                    <td>
                                        @foreach ( $user->regions as $region )
                                            <div class="small">
                                                <a href="{{ route( 'regions.edit', $region->id ) }}">
                                                    {{ $region->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ( $user->roles as $role )
                                            <div class="small">
                                                <a href="{{ route( 'roles.edit', $role->id ) }}">
                                                    {{ $role->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ( $user->active )
                                            <span class="label label-success">
                                                Да
                                            </span>
                                        @else
                                            <span class="label label-danger">
                                                Нет
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route( 'users.edit', $user->id ) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $users->render() }}

                    @else
                        @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                    @endif

                </div>
            </div>
        </div>
        <!-- END TODO CONTENT -->

    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection