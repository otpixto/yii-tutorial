@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'catalog.customers.create', 'catalog.customers.export' ) )
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
                                                    {{ $provider->customers()->count() }}
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
                                    <th>
                                        ФИО
                                    </th>
                                    <th>
                                        Телефон(ы)
                                    </th>
                                    <th>
                                        Адрес
                                    </th>
                                    @if ( \Auth::user()->can( 'tickets.show' ) )
                                        <th class="text-center">
                                            Заявки
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        ЛК
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $customers as $customer )
                                    <tr>
                                        <td>
                                            {{ $customer->getName() }}
                                        </td>
                                        <td>
                                            <span class="small">
                                                {{ $customer->getPhones() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small">
                                                {{ $customer->getActualAddress() }}
                                            </span>
                                        </td>
                                        @if ( \Auth::user()->can( 'tickets.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'tickets.index', [ 'phone' => $customer->phone ] ) }}" class="badge badge-{{ $customer->tickets()->mine()->count() ? 'info' : 'default' }} bold">
                                                    {{ $customer->tickets()->mine()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            <a href="javascript:;" data-customer-lk="{{ $customer->id }}">
                                                @if ( $customer->user && $customer->user->isActive() )
                                                    @include( 'parts.yes' )
                                                @else
                                                    @include( 'parts.no' )
                                                @endif
                                            </a>
                                        </td>
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