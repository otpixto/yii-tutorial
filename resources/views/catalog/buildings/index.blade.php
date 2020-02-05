@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'buildings.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить здание
                </a>
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.buildings.show' ) )

        <div class="todo-ui">
            <div class="todo-sidebar">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption" data-toggle="collapse" data-target="#search">
                            <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                        </div>
                        <a href="{{ route( 'buildings.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
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

                @if ( $buildingTypes->count() > 1 )
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption" data-toggle="collapse" data-target=".todo-project-list-content">
                                <span class="caption-subject font-green-sharp bold uppercase">Типы</span>
                                <span class="caption-helper visible-sm-inline-block visible-xs-inline-block">нажмите, чтоб развернуть</span>
                            </div>
                        </div>
                        <div class="portlet-body todo-project-list-content" style="height: auto;">
                            <div class="todo-project-list">
                                <ul class="nav nav-stacked">
                                    @foreach ( $buildingTypes as $buildingType )
                                        <li @if ( \Input::get( 'building_type_id' ) == $buildingType->id ) class="active" @endif>
                                            <a href="?building_type_id={{ $buildingType->id }}">
                                                {{ $buildingType->name }}
                                                <span class="badge badge-info pull-right">
                                                    {{ $buildingType->buildings()->mine( \App\Models\BaseModel::IGNORE_MANAGEMENT )->count() }}
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

                        @if ( $buildings->count() )

                            <div class="row">
                                <div class="col-md-6">
                                    {{ $buildings->render() }}
                                </div>
                                <div class="col-md-6 text-right margin-top-10 margin-bottom-10">
                                    <span class="label label-info">
                                        Найдено: <b>{{ $buildings->total() }}</b>
                                    </span>
                                    @if ( \Auth::user()->can( 'catalog.buildings.export' ) )
                                        |
                                        <a href="{{ route( 'buildings.export', Request::getQueryString() ) }}">Выгрузить</a>
                                    @endif
                                </div>
                            </div>

                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>
                                    </th>
                                    <th>
                                        Наименование
                                    </th>
                                    <th>
                                        Тип
                                    </th>
                                    @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                        <th class="text-center">
                                            УО
                                        </th>
                                    @endif
                                    <th class="text-center">
                                        ГЖИ
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $buildings as $building )
                                    <tr>
                                        <td>
                                            <label class="mt-checkbox mt-checkbox-outline">
                                                {!! Form::checkbox( 'ids[]', $building->id, false, [ 'class' => 'ticket-checkbox' ] ) !!}
                                                <input type="checkbox">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td>
                                            {{ $building->name }}
                                        </td>
                                        <td>
                                            {{ $building->buildingType->name ?? '-' }}
                                        </td>
                                        @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                                            <td class="text-center">
                                                <a href="{{ route( 'buildings.managements', $building->id ) }}"
                                                   class="badge badge-{{ $building->managements->count() ? 'info' : 'default' }} bold">
                                                    {{ $building->managements->count() }}
                                                </a>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ( $building->mosreg_id )
                                                @include( 'parts.yes' )
                                            @else
                                                @include( 'parts.no' )
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ( \Auth::user()->can( 'catalog.buildings.edit' ) )
                                                <a href="{{ route( 'buildings.edit', $building->id ) }}"
                                                   class="btn btn-info">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $buildings->render() }}
                            <div class="row">
                                <div class="col-md-2 center-align">
                            {!! Form::open( [ 'url' => route( 'buildings.massEdit' ), 'method' => 'get', 'target' => '_blank', 'id' => 'form-checkbox', 'class' => 'hidden' ] ) !!}
                            {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}
                            <button type="submit" class="btn btn-default btn-lg">
                                Изменить сегмент (<span id="ids-count">0</span>)
                            </button>
                            {!! Form::close(); !!}
                                <div class="center-block center-align" style="margin-left: 80px;">
                                    <a href="javascript:;" class="text-default hidden" id="cancel-checkbox">
                                        отмена
                                    </a>
                                </div>
                                </div>
                            </div>

                        @else
                            @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                        @endif

                    </div>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
@section( 'js' )
    <script type="text/javascript">

        function checkTicketCheckbox() {
            $('#form-checkbox').removeClass('hidden');
            $('#cancel-checkbox').removeClass('hidden');
            var ids = [];
            let i = 0;
            $('.ticket-checkbox:checked').each(function () {
                ids.push($(this).val());
                i++;
            });
            $('#ids-count').text(ids.length);
            if (ids.length) {
                $('#controls').fadeIn(300);
                $('#ids').val(ids.join(','));
            } else {
                $('#controls').fadeOut(300);
                $('#ids').val('');
            }
            if (i == 0) {
                $('#form-checkbox').addClass('hidden');
                $('#cancel-checkbox').addClass('hidden');
            }
        };

        function cancelCheckbox() {
            $('.ticket-checkbox').removeAttr('checked');
            checkTicketCheckbox();
        };

        $(document)

            .on('click', '#cancel-checkbox', function (e) {
                e.preventDefault();
                cancelCheckbox();
            })

            .on('submit', '#form-checkbox', function (event) {
                setTimeout(cancelCheckbox, 500);
            })

            .on('change', '.ticket-checkbox', checkTicketCheckbox);

    </script>
@endsection

@else

    @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

@endif

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css"/>
@endsection
