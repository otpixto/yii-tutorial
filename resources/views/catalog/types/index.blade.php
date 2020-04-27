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
            <div class="col-xs-12 col-md-8">
                <a href="{{ route( 'types.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить классификатор
                </a>
            </div>

            @if ( \Auth::user()->can( 'catalog.types.export_all_found' ) && !empty($queryString) )
            <div class="col-xs-12 col-md-2">
                <div class="row margin-top-15">
                    <div class="center-align">
                        {!! Form::open( [ 'url' => route( 'types.export' ), 'method' => 'get', ] ) !!}

                        {!! Form::hidden( 'query_string', $queryString ) !!}

                        <button type="submit" class="btn btn-file btn-sm">
                            выгрузить все найденные
                        </button>
                        {!! Form::close(); !!}
                    </div>
                </div>
            </div>
            @endif

            @if ( \Auth::user()->can( 'catalog.types.export_directory' ) && !empty($queryString) )
                <div class="col-xs-12 col-md-2">
                    <div class="row margin-top-15">
                        <div class="center-align">

                            <a href="{{ route('types.export.directory') }}">
                            <button type="submit" class="btn btn-default btn-sm">
                                выгрузить справочники
                            </button>
                            </a>

                        </div>
                    </div>
                </div>
            @endif
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
                                    @if ( \Auth::user()->can( 'catalog.types.export' ) )
                                        <th>
                                        </th>
                                    @endif
                                    <th width="20%">
                                        Категория
                                    </th>
                                    <th>
                                        Подкатегория / Тип
                                    </th>
                                    @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                        <th class="text-center">
                                            УО
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        ГЖИ
                                    </th>
                                    <th class="text-center">
                                        Необходим акт
                                    </th>
                                    <th class="text-center">
                                        Платно
                                    </th>
                                    <th class="text-center">
                                        Аварийная
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
                                        @if ( \Auth::user()->can( 'catalog.types.export' ) )
                                            <td>
                                                {!! Form::checkbox( 'ids[]', $type->id, false, [ 'class' => 'type-checkbox' ] ) !!}
                                            </td>
                                        @endif
                                        <td>
                                            {{ $type->parent_name ?: '-' }}
                                        </td>
                                        <td>
                                            {{ $type->name }}
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'types.managements', $type->id ) }}"
                                                   class="badge badge-{{ $type->managements->count() ? 'info' : 'default' }} bold">
                                                    {{ $type->managements->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ( $type->mosreg_id )
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
                                            @if ( $type->works )
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

                            <div class="row">
                                <div class="col-md-9">
                                    {{ $types->render() }}
                                </div>

                                <div class="col-md-3">
                                    <div class="row margin-top-15">
                                        <div class="center-align">
                                            {!! Form::open( [ 'url' => route( 'types.export' ), 'method' => 'get', 'id' => 'form-checkbox', 'class' => 'hidden' ] ) !!}
                                            {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}

                                            <button type="submit" class="btn btn-default btn-sm">
                                                Выгрузить в эксель (<span id="ids-count">0</span>)
                                            </button>
                                            {!! Form::close(); !!}
                                            <div class="center-block center-align" style="margin-left: 50px;">
                                                <a href="javascript:;" class="text-default hidden" id="cancel-checkbox">
                                                    отмена
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                        @endif

                    </div>
                </div>
            </div>
            <!-- END TODO CONTENT -->
        </div>

@section( 'js' )
    <script type="text/javascript">

        function checkTicketCheckbox(isAllData = false) {
            $('#form-checkbox').removeClass('hidden');
            $('#cancel-checkbox').removeClass('hidden');

            var ids = [];
            let i = 0;
            $('.type-checkbox:checked').each(function () {
                ids.push($(this).val());
                i++;
            });

            $('#ids-count').text(ids.length);

            if (ids.length) {
                $('#ids').val(ids.join(','));
            } else {
                $('#ids').val('');
            }
            if (i == 0) {
                $('#form-checkbox').addClass('hidden');
                $('#cancel-checkbox').addClass('hidden');
            }

        }

        function cancelCheckbox() {
            $('.type-checkbox').removeAttr('checked');
            checkTicketCheckbox();
        };

        $(document)

            .on('click', '#cancel-checkbox', function (e) {
                e.preventDefault();
                cancelCheckbox();
            })

            .on('change', '.type-checkbox', function () {
                checkTicketCheckbox(false)
            })

    </script>
@endsection

@else

    @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

@endif

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css"/>
@endsection
