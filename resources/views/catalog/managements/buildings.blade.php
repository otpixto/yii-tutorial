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

                    @foreach ( $managementBuildings as $r )
                        <div class="margin-bottom-5">
                            <button type="button" class="btn btn-xs btn-danger" data-delete="management-building"
                                    data-building="{{ $r->id }}">
                                <i class="fa fa-remove"></i>
                            </button>
                            <a href="{{ route( 'buildings.edit', $r->id ) }}">
                                {{ $r->getAddress( true ) }}
                            </a>
                        </div>
                    @endforeach

                    {{ $managementBuildings->render() }}

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

                <div class="row">
                    <div class="col-md-1">
                        {!! Form::model( $management, [ 'method' => 'delete', 'route' => [ 'managements.buildings.empty', $management->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                        <div class="form-group margin-top-15">
                            <div class="col-md-12">
                                {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-2">
                        <div class="form-group margin-top-15">
                            <button class="btn btn-info" id="alOtherOrganization">
                                Привязать ВСЕ к другой организации
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </div>


        <div class="hidden" id="alManagementsListBlock">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Выбрать УО для привязки
                    </h3>
                </div>
                <div class="panel-body">
                    {!! Form::model( null, [ 'method' => 'post', 'route' => 'buildings.managements.massManagementsAdd', 'class' => 'submit-loading' ] ) !!}
                    <input type="hidden" name="buildings[]" value="{{ $managementBuildingsListString }}">
                    <div class="row">
                        <div class="col-md-12">
                            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements" name="managements[]">
                                @foreach ( $availableManagements as $management => $arr )
                                    <optgroup label="{{ $management }}">
                                        @foreach ( $arr as $management_id => $management_name )
                                            <option value="{{ $management_id }}">
                                                {{ $management_name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row margin-top-15">
                        <div class="col-md-12">
                            {!! Form::submit( 'Привязать', [ 'class' => 'btn btn-success' ] ) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
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

                        let form = $('#alManagementsListBlock').html();

                        console.log(form);

                        Swal.fire({
                            title: 'Привязка адресов к УО',
                            icon: 'info',
                            html: form,
                            showCloseButton: true,
                            showCancelButton: false,
                            showConfirmButton: false,
                            focusConfirm: false,
                            confirmButtonText:
                                '',
                            confirmButtonAriaLabel: 'Продолжить оформление заявки',
                            cancelButtonText: '<h6><b>Отмена</b></h6>',
                            cancelButtonAriaLabel: 'Thumbs down'
                        }).then((result) => {

                            if (result.value) {
                                alert(111);
                            }

                        });
                    }

                });
            })

    </script>
@endsection
