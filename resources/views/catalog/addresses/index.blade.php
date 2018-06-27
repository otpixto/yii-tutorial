@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.addresses.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'addresses.create' ) }}" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Добавить здание
                </a>
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.addresses.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target="#search">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'addresses.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
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
                                                    {{ $region->addresses->count() }}
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

                        @if ( $addresses->count() )

                            {{ $addresses->render() }}

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>
                                        Адрес
                                    </th>
                                    @if ( \Auth::user()->can( 'admin.regions.show' ) )
                                        <th class="text-center">
                                            Регионы
                                        </th>
                                    @endif
                                    @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                        <th class="text-center">
                                            УО
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        GUID
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $addresses as $address )
                                    <tr>
                                        <td>
                                            {{ $address->getAddress() }}
                                        </td>
                                        @if ( \Auth::user()->can( 'admin.regions.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'addresses.regions', $address->id ) }}" class="badge badge-{{ $address->regions()->mine()->count() ? 'info' : 'default' }} bold">
                                                    {{ $address->regions()->mine()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'addresses.managements', $address->id ) }}" class="badge badge-{{ $address->managements()->mine()->count() ? 'info' : 'default' }} bold">
                                                    {{ $address->managements()->mine()->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ( $address->guid )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.addresses.edit' ) )
                                                <a href="{{ route( 'addresses.edit', $address->id ) }}" class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $addresses->render() }}

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