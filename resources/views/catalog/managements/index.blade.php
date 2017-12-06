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
                                <th>
                                    Регион
                                </th>
                                <th>
                                    Категория
                                </th>
                                <th>
                                    Наименование
                                </th>
                                <th>
                                    GUID
                                </th>
                                <th class="text-center">
                                    Есть договор
                                </th>
                                <th class="text-center">
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
                                        {{ $management->region->name }}
                                    </td>
                                    <td>
                                        {{ $management->getCategory() }}
                                    </td>
                                    <td>
                                        {{ $management->name }}
                                    </td>
                                    <td>
                                        {{ $management->guid }}
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
                                        <a href="{{ route( 'managements.edit', $management->id ) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
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

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection