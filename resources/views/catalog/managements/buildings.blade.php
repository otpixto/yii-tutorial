@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        <div class="well">
            <a href="{{ route( 'managements.edit', $management->id ) }}">
                @if ( $management->parent )
                    <div class="text-muted">
                        {{ $management->parent->name }}
                    </div>
                @endif
                {{ $management->name }}
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-plus"></i>
                            Добавить Здания из сегмента
                        </h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.segments.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                <div id="segment_id" data-name="segments[]"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'type_id', 'Тип здания', [ 'class' => 'control-label col-md-4' ] ) !!}
                            <div class="col-md-4">
                                {!! Form::select( 'type_id', $buildingTypes, '', [ 'class' => 'form-control select2', 'id' => 'type_id', 'placeholder' => 'ВСЕ' ] ) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-plus"></i>
                            Добавить Здания
                        </h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.buildings.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::select( 'buildings[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'managements.buildings.search', $management->id ), 'multiple' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-search"></i>
                    Поиск
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'managements.buildings', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::text( 'search', $search, [ 'class' => 'form-control' ] ) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::submit( 'Найти', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                @if ( $managementBuildings->count() )

                    <div class="row">
                        <div class="col-md-6">
                            {{ $managementBuildings->render() }}
                        </div>
                        <div class="col-md-6 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $managementBuildings->total() }}</b>
                        </span>
                            @if ( \Auth::user()->can( 'catalog.buildings.export' ) )
                                |
                                <a href="{{ route( 'managements.buildings.export', [ $management->id, Request::getQueryString() ] ) }}">Выгрузить</a>
                            @endif
                        </div>
                    </div>

                    @foreach ( $managementBuildings as $managementBuilding )
                        <div class="margin-bottom-5">
                            <label class="mt-checkbox mt-checkbox-outline">
                                {!! Form::checkbox( 'ids[]', $managementBuilding->id, false, [ 'class' => 'ticket-checkbox' ] ) !!}
                                <input type="checkbox">
                                <span></span>
                            </label>
                            <a href="{{ route( 'buildings.edit', $managementBuilding->id ) }}">
                                {{ $managementBuilding->getAddress( true ) }}
                            </a>
                        </div>
                    @endforeach

                    {{ $managementBuildings->render() }}

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

                <div class="row">
                    <div class="col-md-1 center-align">
                        {!! Form::model( $management, [ 'method' => 'delete', 'route' => [ 'managements.buildings.empty', $management->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                        <div class="form-group margin-top-15">
                            <div class="col-md-12">
                                {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-3 center-align">
                        <div class="form-group margin-top-15">
                            <button class="btn btn-info" id="alOtherOrganization">
                                Привязать ВСЕ к другой организации
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        {!! Form::open( [ 'url' => route( 'buildings.massEdit' ), 'method' => 'get', 'target' => '_blank', 'id' => 'form-checkbox', 'class' => 'hidden' ] ) !!}
                        {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}
                        <div class="form-group margin-top-15">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-default">
                                    Изменить сегмент (<span id="ids-count">0</span>)
                                </button>
                            </div>
                        </div>
                        {!! Form::close(); !!}
                        <div class="center-block center-align" style="margin-left: 80px;">
                            <a href="javascript:;" class="text-default hidden" id="cancel-checkbox">
                                отмена
                            </a>
                        </div>
                    </div>

                    <div class="col-md-2">
                        {!! Form::open( [ 'route' => [ 'buildings.mass-buildings-delete', $management->id ], 'method' => 'get', 'class' => 'hidden', 'data-confirm' => 'Вы уверены?', 'id' => 'form-checkbox-delete' ] ) !!}

                        {!! Form::hidden( 'ids', null, [ 'id' => 'ids-delete' ] ) !!}

                        <div class="form-group margin-top-15">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-danger">
                                    Удалить привязку (<span id="ids-count-delete">0</span>)
                                </button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                        <div class="center-block center-align" style="margin-left: 80px;">
                            <a href="javascript:;" class="text-default hidden" id="cancel-checkbox-delete">
                                отмена
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet"
          type="text/css"/>
@endsection

@section( 'js' )
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js"
            type="text/javascript"></script>
    <script type="text/javascript">


        function checkTicketCheckbox() {
            $('#form-checkbox').removeClass('hidden');

            $('#form-checkbox-delete').removeClass('hidden');

            $('#cancel-checkbox').removeClass('hidden');

            $('#cancel-checkbox-delete').removeClass('hidden');
            var ids = [];
            let i = 0;
            $('.ticket-checkbox:checked').each(function () {
                ids.push($(this).val());
                i++;
            });
            $('#ids-count').text(ids.length);
            $('#ids-count-delete').text(ids.length);
            if (ids.length) {
                $('#controls').fadeIn(300);
                $('#ids').val(ids.join(','));
                $('#ids-delete').val(ids.join(','))
            } else {
                $('#controls').fadeOut(300);
                $('#ids').val('');
                $('#ids-delete').val('');
            }
            if (i == 0) {
                $('#form-checkbox').addClass('hidden');
                $('#form-checkbox-delete').addClass('hidden');
                $('#cancel-checkbox').addClass('hidden');
                $('#cancel-checkbox-delete').addClass('hidden');
            }
        };

        function cancelCheckbox() {
            $('.ticket-checkbox').removeAttr('checked');
            checkTicketCheckbox();
        };

        $(document)

            .ready(function () {

                $('#segment_id').selectSegments();

                $('.mt-multiselect').multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on('click', '#cancel-checkbox, #cancel-checkbox-delete', function (e) {
                e.preventDefault();
                cancelCheckbox();
            })

            .on('submit', '#form-checkbox', function (event) {
                setTimeout(cancelCheckbox, 500);
            })

            .on('change', '.ticket-checkbox', checkTicketCheckbox)

            .on('click', '[data-delete="management-building"]', function (e) {

                e.preventDefault();

                var building_id = $(this).attr('data-building');
                var obj = $(this).closest('div');

                bootbox.confirm({
                    message: 'Удалить привязку?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'managements.buildings.del', $management->id ) }}',
                                method: 'delete',
                                data: {
                                    building_id: building_id
                                },
                                success: function () {
                                    obj.remove();
                                },
                                error: function (e) {
                                    obj.show();
                                    alert(e.statusText);
                                }
                            });

                        }
                    }
                });

            })

            .on('click', '#alOtherOrganization', function () {
                Swal.fire({
                    title: '',
                    icon: 'info',
                    html: '<h6><b>Вы уверены?</b></h6>',
                    showCloseButton: true,
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText:
                        '<h6><b>ОК</b></h6>',
                    confirmButtonAriaLabel: 'ОК',
                    cancelButtonText: '<h6><b>Отмена</b></h6>',
                    cancelButtonAriaLabel: 'Thumbs down'
                }).then((result) => {

                    if (result.value) {
                        window.location.href = '{{ route('buildings.managements.massManagementsEdit', [ 'management_id' => $management->id ]) }}';

                        return false;
                    }

                });
            })

    </script>
@endsection
