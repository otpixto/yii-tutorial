@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( [ 'catalog.customers.create', 'catalog.customers.export' ] ) )
        <div class="row margin-bottom-15 hidden-print">
            <div class="col-xs-6">
                @if ( \Auth::user()->can( 'catalog.customers.create' ) )
                    <a href="{{ route( 'customers.create' ) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-plus"></i>
                        Добавить заявителя
                    </a>
                @endif
            </div>
            <div class="col-xs-6 text-right">
                @if ( \Auth::user()->can( 'catalog.customers.export' ) )
                    <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                        <i class="fa fa-download"></i>
                        Выгрузить в Excel
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.customers.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target="#search">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'customers.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
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
                            {!! Form::hidden( 'region', \Input::get( 'region' ) ) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                @if ( $regions->count() > 1 )
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
                                                    {{ $region->customers->count() }}
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

                        @if ( $customers->count() )

                            {{ $customers->render() }}

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    @if ( $regions->count() > 1 )
                                        <th>
                                            Регион
                                        </th>
                                    @endif
                                    <th>
                                        ФИО
                                    </th>
                                    <th>
                                        Телефон(ы)
                                    </th>
                                    <th>
                                        Адрес
                                    </th>
                                    @if ( \Auth::user()->can( 'catalog.customers.tickets' ) )
                                        <th class="text-center">
                                            Заявки
                                        </th>
                                    @endif
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $customers as $customer )
                                    <tr>
                                        @if ( $regions->count() > 1 )
                                            <td>
                                                {{ $customer->region->name }}
                                            </td>
                                        @endif
                                        <td>
                                            {{ $customer->getName() }}
                                        </td>
                                        <td>
                                            {{ $customer->getPhones() }}
                                        </td>
                                        <td>
                                            {{ $customer->getAddress() }}
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.customers.tickets' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'tickets.customer_tickets', $customer->id ) }}" class="badge badge-{{ $customer->tickets->count() ? 'info' : 'default' }} bold">
                                                    {{ $customer->tickets->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.customers.edit' ) )
                                                <a href="{{ route( 'customers.edit', $customer->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $customers->render() }}

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