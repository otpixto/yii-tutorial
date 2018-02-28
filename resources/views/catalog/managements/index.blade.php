@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( [ 'catalog.managements.create', 'catalog.managements.export' ] ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-6">
                @if ( \Auth::user()->can( 'catalog.managements.create' ) )
                    <a href="{{ route( 'managements.create' ) }}" class="btn btn-success">
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
                            {!! Form::hidden( 'region', \Input::get( 'region' ) ) !!}
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
                                    <li @if ( \Input::get( 'category' ) == $category_id ) class="active" @endif>
                                        <a href="?category={{ $category_id }}">
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

                @if ( $regions->count() > 1 )
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                                <span class="caption-subject font-green-sharp bold uppercase">РЕГИОНЫ</span>
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
                                                    {{ $region->managements->count() }}
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

                            {{ $managements->render() }}

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    @if ( $regions->count() > 1 )
                                        <th width="10%">
                                            Регион
                                        </th>
                                    @endif
                                    <th width="10%">
                                        Категория
                                    </th>
                                    <th width="20%">
                                        Наименование
                                    </th>
                                    <th>
                                        Адрес \ телефон(ы)
                                    </th>
                                    <th class="text-center">
                                        Адреса
                                    </th>
                                    <th class="text-center">
                                        Классификатор
                                    </th>
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
                                        @if ( $regions->count() > 1 )
                                            <td>
                                                {{ $management->region->name }}
                                            </td>
                                        @endif
                                        <td>
                                            {{ $management->getCategory() }}
                                        </td>
                                        <td>
                                            {{ $management->name }}
                                        </td>
                                        <td>
                                            @if ( $management->address )
                                                <div>
                                                    {{ $management->address->name }}
                                                </div>
                                            @endif
                                            <div class="margin-top-10">
                                                {!! $management->getPhones( true ) !!}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route( 'addresses.index', [ 'management' => $management->id ] ) }}" class="badge badge-{{ $management->addresses->count() ? 'info' : 'default' }} bold">
                                                {{ $management->addresses->count() }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route( 'types.index', [ 'management' => $management->id ] ) }}" class="badge badge-{{ $management->types->count() ? 'info' : 'default' }} bold">
                                                {{ $management->types->count() }}
                                            </a>
                                        </td>
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