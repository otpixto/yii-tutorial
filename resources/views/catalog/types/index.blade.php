@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.types.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'types.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить классификатор
                </a>
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.types.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'types.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
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
                            {!! Form::hidden( 'category_id', \Input::get( 'category_id' ) ) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target="#search-categories">
                            <span class="caption-subject font-green-sharp bold uppercase">КАТЕГОРИИ</span>
                            <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                        </div>
                    </div>
                    <div class="portlet-body todo-project-list-content" id="search-categories" style="height: auto;">
                        <div class="todo-project-list">
                            <ul class="nav nav-stacked">
                                @foreach ( $parents as $parent )
                                    <li @if ( \Input::get( 'parent_id' ) == $parent->id ) class="active" @endif>
                                        <a href="?parent_id={{ $parent->id }}">
                                            {{ $parent->name }}
                                            <span class="badge badge-info pull-right">
                                                {{ $parent->childs()->count() }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                @if ( $providers->count() > 1 )
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption" data-toggle="collapse" data-target="#search-providers">
                                <span class="caption-subject font-green-sharp bold uppercase">Поставщики</span>
                                <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                            </div>
                        </div>
                        <div class="portlet-body todo-project-list-content" id="search-providers" style="height: auto;">
                            <div class="todo-project-list">
                                <ul class="nav nav-stacked">
                                    @foreach ( $providers as $provider )
                                        <li @if ( \Input::get( 'provider_id' ) == $provider->id ) class="active" @endif>
                                            <a href="?provider_id={{ $provider->id }}">
                                                {{ $provider->name }}
                                                <span class="badge badge-info pull-right">
                                                    {{ $provider->types()->count() }}
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

            <!-- BEGIN TODO CONTENT -->
            <div class="todo-content">
                <div class="portlet light ">
                    <div class="portlet-body">

                        @if ( $types->count() )

                            <div class="row">
                                <div class="col-md-8">
                                    {{ $types->render() }}
                                </div>
                                <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                                    <span class="label label-info">
                                        Найдено: <b>{{ $types->total() }}</b>
                                    </span>
                                </div>
                            </div>

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th width="20%">
                                        Родитель
                                    </th>
                                    <th>
                                        Наименование
                                    </th>
                                    @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                        <th class="text-center">
                                            УО
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        GUID
                                    </th>
                                    <th class="text-center">
                                        Необходим акт
                                    </th>
                                    <th class="text-center">
                                        Платно
                                    </th>
                                    <th class="text-center">
                                        Авария
                                    </th>
                                    <th class="text-center">
                                        Отключения
                                    </th>
                                    <th class="text-center">
                                        ЛК
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $types as $type )
                                    <tr>
                                        <td>
                                            {{ $type->parent_name ?: '-' }}
                                        </td>
                                        <td>
                                            {{ $type->name }}
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'types.managements', $type->id ) }}" class="badge badge-{{ $type->managements->count() ? 'info' : 'default' }} bold">
                                                    {{ $type->managements->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ( $type->guid )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $type->need_act )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $type->is_pay )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $type->emergency )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $type->work )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $type->lk )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.types.edit' ) )
                                                <a href="{{ route( 'types.edit', $type->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $types->render() }}

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