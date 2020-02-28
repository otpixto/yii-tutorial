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

        <div class="row margin-top-15 hidden-print">
            <div class="col-xs-12">
                <div class="portlet box blue-hoki">
                    <div class="portlet-title">
                        <a class="caption" data-load="search" data-toggle="#search">
                            <i class="fa fa-search"></i>
                            ПОИСК
                        </a>
                    </div>
                    <div class="portlet-body hidden" id="search"></div>
                </div>
            </div>
        </div>


        <div class="todo-ui">

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
                                        Сегмент
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
                                            {{ isset($building->segment) ? $building->segment->getName(true) : '' }}
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

                            <div class="row">
                                <div class="col-md-6">
                                    {{ $buildings->render() }}
                                </div>
                                <div class="col-md-3">
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
                                </div>

                                <div class="col-md-3 center-align">
                                    <div class="form-group">
                                        {!! Form::open( [ 'url' => route('buildings.managements.massManagementsEdit', [ 'management_id' => null ]), 'method' => 'get', 'target' => '_blank', 'id' => 'form-checkbox-bind', 'class' => 'hidden' ] ) !!}
                                        {!! Form::hidden( 'ids', null, [ 'id' => 'ids-bind' ] ) !!}
                                        <button type="submit" class="btn btn-info btn-lg">
                                            Привязать ВСЕ к другой организации
                                        </button>
                                        {!! Form::close(); !!}
                                        <div class="center-block center-align" style="margin-left: 150px;">
                                            <a href="javascript:;" class="text-default hidden"
                                               id="cancel-checkbox-bind">
                                                отмена
                                            </a>
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
            <!-- END CONTENT -->
        </div>
@section( 'js' )
    <script type="text/javascript">

        function checkTicketCheckbox() {
            $('#form-checkbox').removeClass('hidden');
            $('#cancel-checkbox').removeClass('hidden');

            $('#form-checkbox-bind').removeClass('hidden');
            $('#cancel-checkbox-bind').removeClass('hidden');

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
                $('#ids-bind').val(ids.join(','));
            } else {
                $('#controls').fadeOut(300);
                $('#ids').val('');
                $('#ids-bind').val('');
            }
            if (i == 0) {
                $('#form-checkbox').addClass('hidden');
                $('#cancel-checkbox').addClass('hidden');

                $('#form-checkbox-bind').addClass('hidden');
                $('#cancel-checkbox-bind').addClass('hidden');
            }
        };

        function cancelCheckbox() {
            $('.ticket-checkbox').removeAttr('checked');
            checkTicketCheckbox();
        };

        $(document)

            .on('click', '#cancel-checkbox, #cancel-checkbox-bind', function (e) {
                e.preventDefault();
                cancelCheckbox();
            })

            .on('submit', '#form-checkbox, #form-checkbox-bind', function (event) {
                setTimeout(cancelCheckbox, 500);
            })

            .on('change', '.ticket-checkbox', checkTicketCheckbox)


            .on('click', '[data-load="search"]', function (e) {
                e.preventDefault();
                if ($('#search').text().trim() == '') {
                    $('#search').loading();
                    $.get('{{ route( 'buildings.search.form' ) }}', window.location.search, function (response) {
                        $('#search').html(response);
                        $('.select2').select2();
                        $('.select2-ajax').select2({
                            minimumInputLength: 3,
                            minimumResultsForSearch: 30,
                            ajax: {
                                cache: true,
                                type: 'post',
                                delay: 450,
                                data: function (term) {
                                    var data = {
                                        q: term.term,
                                        provider_id: $('#provider_id').val()
                                    };
                                    var _data = $(this).closest('form').serializeArray();
                                    for (var i = 0; i < _data.length; i++) {
                                        if (_data[i].name != '_method') {
                                            data[_data[i].name] = _data[i].value;
                                        }
                                    }
                                    return data;
                                },
                                processResults: function (data, page) {
                                    return {
                                        results: data
                                    };
                                }
                            }
                        });

                        $('.mask_phone').inputmask('mask', {
                            'mask': '+7 (999) 999-99-99'
                        });

                        $('#segment_id').selectSegments();

                    });
                }
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
