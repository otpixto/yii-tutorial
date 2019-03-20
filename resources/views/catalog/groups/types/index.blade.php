@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.groups.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'types_groups.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить группу
                </a>
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.groups.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target="#search">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'types_groups.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                    </div>
                    <div class="portlet-body todo-project-list-content" id="search" style="height: auto;">
                        <div class="todo-project-list">
                            {!! Form::open( [ 'method' => 'get' ] ) !!}
                            <div class="row">
                                <div class="col-xs-12">
                                    {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-xs-12">
                                    {!! Form::submit( 'Найти', [ 'class' => 'btn btn-info btn-block' ] ) !!}
                                </div>
                            </div>
                            {!! Form::hidden( 'provider_id', \Input::get( 'provider_id' ) ) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                @if ( $providers->count() > 1 )
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                                <span class="caption-subject font-green-sharp bold uppercase">Поставщики</span>
                                <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                            </div>
                        </div>
                        <div class="portlet-body todo-project-list-content" style="height: auto;">
                            <div class="todo-project-list">
                                <ul class="nav nav-stacked">
                                    @foreach ( $providers as $provider )
                                        <li @if ( \Input::get( 'provider_id' ) == $provider->id ) class="active" @endif>
                                            <a href="?provider_id={{ $provider->id }}">
                                                {{ $provider->name }}
                                                <span class="badge badge-info pull-right">
                                                    {{ $provider->typesGroups()->count() }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            <!-- END TODO SIDEBAR -->

            <!-- BEGIN CONTENT -->
            <div class="todo-content">
                <div class="portlet light ">
                    <div class="portlet-body">

                        @if ( $groups->count() )

                            <div class="row">
                                <div class="col-md-8">
                                    {{ $groups->render() }}
                                </div>
                                <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                                    <span class="label label-info">
                                        Найдено: <b>{{ $groups->total() }}</b>
                                    </span>
                                </div>
                            </div>

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>
                                        Наименование
                                    </th>
                                    <th class="text-center">
                                        Адресов
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $groups as $group )
                                    <tr>
                                        <td>
                                            {{ $group->name }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route( 'groups.buildings', $group->id ) }}" class="badge badge-{{ $group->buildings->count() ? 'info' : 'default' }} bold">
                                                {{ $group->buildings->count() }}
                                            </a>
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.groups.edit' ) )
                                                <a href="{{ route( 'groups.edit', $group->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $groups->render() }}

                        @else
                            @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                        @endif

                    </div>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection