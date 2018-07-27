@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'catalog.managements.create', 'catalog.managements.export' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-6">
                @if ( \Auth::user()->can( 'catalog.managements.create' ) )
                    <a href="{{ route( 'managements.create' ) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-plus"></i>
                        Добавить УО
                    </a>
                @endif
            </div>
            <div class="col-xs-6 text-right">
                @if ( \Auth::user()->can( 'catalog.managements.export' ) )
                    <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                        <i class="fa fa-download"></i>
                        Выгрузить в Excel
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.managements.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'managements.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
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
                            {!! Form::hidden( 'provider_id', \Input::get( 'provider_id' ) ) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                            <span class="caption-subject font-green-sharp bold uppercase">КАТЕГОРИИ</span>
                            <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                        </div>
                    </div>
                    <div class="portlet-body todo-project-list-content" style="height: auto;">
                        <div class="todo-project-list">
                            <ul class="nav nav-stacked">
                                @foreach ( \App\Models\Management::$categories as $category_id => $name )
                                    <li @if ( \Input::get( 'category_id' ) == $category_id ) class="active" @endif>
                                        <a href="?category_id={{ $category_id }}">
                                            {{ $name }}
                                            <span class="badge badge-info pull-right">
                                                {{ \App\Models\Management::mine()->category( $category_id )->count() }}
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
                                                    {{ $provider->managements()->count() }}
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

                        @if ( $managements->count() )

                            <div class="row">
                                <div class="col-md-8">
                                    {{ $managements->render() }}
                                </div>
                                <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                                    <span class="label label-info">
                                        Найдено: <b>{{ $managements->total() }}</b>
                                    </span>
                                </div>
                            </div>

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th width="10%">
                                        Категория
                                    </th>
                                    <th width="20%">
                                        Наименование
                                    </th>
                                    <th>
                                        Адрес \ телефон(ы)
                                    </th>
                                    @if ( \Auth::user()->can( 'catalog.buildings..show' ) )
                                        <th class="text-center">
                                            Адреса
                                        </th>
                                    @endif
                                    @if ( \Auth::user()->can( 'catalog.types.show' ) )
                                        <th class="text-center">
                                            Классификатор
                                        </th>
                                    @endif
                                    @if ( \Auth::user()->can( 'catalog.managements.executors.show' ) )
                                        <th class="text-center">
                                            Исполнители
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        GUID
                                    </th>
                                    <th class="text-center" width="80">
                                        Есть договор
                                    </th>
                                    <th class="text-center" width="80">
                                        Оповещения в Telegram
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $managements as $management )
                                    <tr>
                                        <td>
                                            {{ $management->getCategory() }}
                                        </td>
                                        <td>
                                            @if ( $management->parent )
                                                <div class="text-muted">
                                                    {{ $management->parent->name }}
                                                </div>
                                            @endif
                                            {{ $management->name }}
                                        </td>
                                        <td>
                                            @if ( $management->building )
                                                <div>
                                                    {{ $management->building->name }}
                                                </div>
                                            @endif
                                            <div class="margin-top-10">
                                                {!! $management->getPhones( true ) !!}
                                            </div>
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.buildings..show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'managements.buildings.', $management->id ) }}" class="badge badge-{{ $management->buildings()->count() ? 'info' : 'default' }} bold">
                                                    {{ $management->buildings()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        @if ( \Auth::user()->can( 'catalog.types.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'managements.types', $management->id ) }}" class="badge badge-{{ $management->types()->count() ? 'info' : 'default' }} bold">
                                                    {{ $management->types()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        @if ( \Auth::user()->can( 'catalog.managements.executors.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'managements.executors', $management->id ) }}" class="badge badge-{{ $management->executors()->count() ? 'info' : 'default' }} bold">
                                                    {{ $management->executors()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ( $management->guid )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $management->has_contract )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ( $management->telegram_code )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.managements.edit' ) )
                                                <a href="{{ route( 'managements.edit', $management->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $managements->render() }}

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