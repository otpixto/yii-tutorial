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

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить Классификатор
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.types.add', $management->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">
                                -
                            </option>
                            @foreach ( $availableCategories as $category )
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control hidden" multiple id="types" name="types[]"></select>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
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
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'managements.types', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $managementTypes->render() }}

                @if ( ! $managementTypes->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif
                @foreach ( $managementTypes as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="management-type"
                                data-type="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'types.edit', $r->id ) }}">
                            {{ $r->name }}
                        </a>
                    </div>
                @endforeach

                {{ $managementTypes->render() }}

                <div class="row">
                    <div class="col-md-1 center-align">
                        {!! Form::model( $management, [ 'method' => 'delete', 'route' => [ 'managements.types.empty', $management->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

                $('#category_id')
                    .select2()
                    .on('select2:select', function (e) {
                        var data = e.params.data;
                        $.post('{{ route( 'managements.types', $management->id ) }}', {
                            parent_id: data.id
                        }, function (response) {
                            $('#types').empty();
                            $.each(response, function (i, item) {
                                $('#types').append(
                                    $('<option>').val(item.id).text(item.text)
                                );
                            });
                            $('#types').removeClass('hidden');
                            $('#types').multiselect('destroy');
                            $('#types').multiselect({
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
                        });
                    });

            })

            .on('click', '[data-delete="management-type"]', function (e) {

                e.preventDefault();

                var type_id = $(this).attr('data-type');
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
                                url: '{{ route( 'managements.types.del', $management->id ) }}',
                                method: 'delete',
                                data: {
                                    type_id: type_id
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
                        window.location.href = '{{ route('types.managements.massManagementsEdit', [ 'management_id' => $management->id ]) }}';

                        return false;
                    }

                });
            })

    </script>
@endsection
