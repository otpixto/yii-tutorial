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
                <a href="{{ route( 'categories.create' ) }}" class="btn btn-info btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить категорию
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
                        <div class="caption" data-toggle="collapse" data-target="#categories">
                            <span class="caption-subject font-green-sharp bold uppercase">КАТЕГОРИИ</span>
                            <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                        </div>
                    </div>
                    <div class="portlet-body todo-project-list-content" id="categories" style="height: auto;">
                        <div class="todo-project-list">
                            <ul class="nav nav-stacked">
                                @foreach ( $categories as $category )
                                    <li @if ( \Input::get( 'category_id' ) == $category->id ) class="active" @endif>
                                        <a href="?category_id={{ $category->id }}">
                                            {{ $category->name }}
                                            <span class="badge badge-info pull-right">
                                                {{ $category->types()->count() }}
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

                        @if ( $types->count() )

                            {{ $types->render() }}

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th width="20%">
                                        Категория
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
                                    <th class="text-center" width="150">
                                        Необходим акт
                                    </th>
                                    <th class="text-center" width="80">
                                        Платно
                                    </th>
                                    <th class="text-center" width="80">
                                        Авария
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
                                            <a href="{{ route( 'categories.edit', $type->category_id ) }}">
                                                {{ $type->category_name }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $type->name }}
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'types.managements', $type->id ) }}" class="badge badge-{{ $type->managements()->mine()->count() ? 'info' : 'default' }} bold">
                                                    {{ $type->managements()->mine()->count() }}
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