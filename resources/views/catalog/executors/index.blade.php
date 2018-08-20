@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'catalog.executors.create', 'catalog.executors.export' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-6">
                @if ( \Auth::user()->can( 'catalog.executors.create' ) )
                    <a href="{{ route( 'executors.create' ) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-plus"></i>
                        Добавить Исполнителя
                    </a>
                @endif
            </div>
            <div class="col-xs-6 text-right">
                @if ( \Auth::user()->can( 'catalog.executors.export' ) )
                    <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                        <i class="fa fa-download"></i>
                        Выгрузить в Excel
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.executors.show' ) )

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

            </div>
            <!-- END TODO SIDEBAR -->

            <!-- BEGIN TODO CONTENT -->
            <div class="todo-content">
                <div class="portlet light ">
                    <div class="portlet-body">

                        @if ( $executors->count() )

                            <div class="row">
                                <div class="col-md-8">
                                    {{ $executors->render() }}
                                </div>
                                <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                                    <span class="label label-info">
                                        Найдено: <b>{{ $executors->total() }}</b>
                                    </span>
                                </div>
                            </div>

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th width="10%">
                                        УО
                                    </th>
                                    <th width="20%">
                                        Наименование
                                    </th>
                                    <th>
                                        Телефон
                                    </th>
                                    <th class="text-center">
                                        Заявки
                                    </th>
                                    <th class="text-center">
                                        Отключения
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $executors as $executor )
                                    <tr>
                                        <td>
                                            <a href="{{ route( 'managements.edit', $executor->management_id ) }}">
                                                @if ( $executor->management->parent )
                                                    <div class="text-muted">
                                                        {{ $executor->management->parent->name }}
                                                    </div>
                                                @endif
                                                {{ $executor->management->name }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $executor->name }}
                                        </td>
                                        <td>
                                            {{ $executor->getPhone() }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route( 'tickets.index', [ 'executor_id' => $executor->id ] ) }}" class="badge badge-{{ $executor->tickets->count() ? 'info' : 'default' }} bold">
                                                {{ $executor->tickets->count() }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route( 'works.index', [ 'show' => 'all', 'executor_id' => $executor->id ] ) }}" class="badge badge-{{ $executor->works->count() ? 'info' : 'default' }} bold">
                                                {{ $executor->works->count() }}
                                            </a>
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.executors.edit' ) )
                                                <a href="{{ route( 'executors.edit', $executor->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $executors->render() }}

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