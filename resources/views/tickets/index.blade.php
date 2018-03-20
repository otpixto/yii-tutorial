@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( [ 'tickets.show', 'tickets.all' ] ) )

        @if( \Auth::user()->canOne( [ 'tickets.create', 'tickets.export' ] ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @if( \Auth::user()->can( 'tickets.create' ) )
                        <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить заявку
                        </a>
                    @endif
                </div>
                <div class="col-xs-6 text-right">
                    @if( \Auth::user()->can( 'tickets.export' ) )
                        <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <div class="row hidden-print">
            <div class="col-xs-12">
                {!! Form::open( [ 'method' => 'get' ] ) !!}
                    <div class="input-group">
                        {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control input-lg', 'placeholder' => 'Быстрый поиск...' ] ) !!}
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-search"></i>
                                Поиск
                            </button>
                        </span>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="row margin-top-15 hidden-print">
            <div class="col-xs-12">
                <div class="portlet box blue-hoki">
                    <div class="portlet-title">
                        <div class="caption">
                            Быстрый фильтр по статусам
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="expand" data-original-title="Показать\Скрыть" title="Показать\Скрыть"> </a>
                        </div>
                    </div>
                    <div class="portlet-body portlet-collapsed">
                        @foreach ( \Auth::user()->getAvailableStatuses( 'show', true, true ) as $status_code => $status_name )
                            @if ( $status_code != 'draft' )
                                <a href="{{ route( 'tickets.index', compact( 'status_code' ) ) }}" class="margin-bottom-10 btn btn-{{ $status_code == \Input::get( 'status_code' ) ? 'info' : 'default' }}">
                                    {{ $status_name }}
                                    <span class="badge bold">
                                    {{ \App\Models\TicketManagement::getCountByStatus( $status_code ) }}
                                </span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-md-8">
                        {{ $ticketManagements->render() }}
                    </div>
                    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $ticketManagements->total() }}</b>
                        </span>
                    </div>
                </div>

                <table class="table table-striped table-bordered table-hover">
                    {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                    <thead>
                        <tr class="info">
                            <th width="250">
                                 Статус \ Номер заявки \ Оценка
                            </th>
                            <th width="220">
                                Дата и время создания
                            </th>
                            @if ( $field_operator )
                                <th width="150">
                                    Оператор
                                </th>
                            @endif
                            <th width="200">
                                @if ( $field_management )
                                    УО \
                                @endif
                                Исполнитель
                            </th>
                            <th width="300">
                                Тип заявки
                                @if ( \Auth::user()->can( 'tickets.works.show' ) )
                                    \ Выполненные работы
                                @endif
                            </th>
                            <th colspan="2">
                                Адрес проблемы
                                <a href="javascript:;" class="pull-right toggle _expand" data-toggle="#filters">&nbsp;</a>
                            </th>
                        </tr>
                        <tr class="info hidden-print" style="display: none;" id="filters">
                            <td>
                                <div class="row">
                                    <div class="col-lg-12">
                                        {!! Form::select( 'status_code', [ null => ' -- все -- ' ] + \Auth::user()->getAvailableStatuses( 'show', true, true ), \Input::old( 'status_code' ), [ 'class' => 'form-control select2', 'placeholder' => 'Статус' ] ) !!}
                                    </div>
                                </div>
                                <div class="row margin-top-10">
                                    <div class="col-lg-8">
                                        {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
                                    </div>
                                    <div class="col-lg-4">
                                        {!! Form::select( 'rate', [ 0 => '-', 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 ], \Input::old( 'rate' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Оценка' ] ) !!}
                                    </div>
                                </div>
                                <div class="row margin-top-10">
                                    <div class="col-lg-12">
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            Просрочено на принятие
                                            {!! Form::checkbox( 'overdue_acceptance', 1, \Input::old( 'overdue_acceptance' ) ) !!}
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            Просрочено на исполнение
                                            {!! Form::checkbox( 'overdue_execution', 1, \Input::old( 'overdue_execution' ) ) !!}
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="date-picker input-daterange" data-date-format="dd.mm.yyyy">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    {!! Form::text( 'period_from', \Input::old( 'period_from' ), [ 'class' => 'form-control', 'placeholder' => 'ОТ' ] ) !!}
                                                </div>
                                            </div>
                                            <div class="row margin-top-10">
                                                <div class="col-lg-12">
                                                    {!! Form::text( 'period_to', \Input::old( 'period_to' ), [ 'class' => 'form-control', 'placeholder' => 'ДО' ] ) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if ( $field_operator )
                                <td>
                                    {!! Form::select( 'operator_id', [ null => ' -- все -- ' ] + $operators, \Input::old( 'operator_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Оператор' ] ) !!}
                                </td>
                            @endif
                            <td>
                                @if ( $field_management )
                                    <div class="row">
                                        <div class="col-lg-12">
                                            {!! Form::select( 'management_id', [ null => ' -- все -- ' ] + $managements, \Input::old( 'management_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'УО' ] ) !!}
                                        </div>
                                    </div>
                                @endif
                                <div class="row margin-top-10">
                                    <div class="col-lg-12">
                                        {!! Form::select( 'executor_id', [ null => ' -- все -- ' ] + $executors, \Input::old( 'executor_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Исполнитель' ] ) !!}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-lg-12">
                                        {!! Form::select( 'type', [ null => ' -- все -- ' ] + $types, \Input::old( 'type' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип заявки' ] ) !!}
                                    </div>
                                </div>
                                <div class="row margin-top-10">
                                    <div class="col-lg-12">
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <i class="icon-fire"></i>
                                            Авария
                                            {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency' ) ) !!}
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <i class="icon-heart"></i>
                                            Добродел
                                            {!! Form::checkbox( 'dobrodel', 1, \Input::old( 'dobrodel' ) ) !!}
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <i class="icon-user-follow"></i>
                                            Из ЛК
                                            {!! Form::checkbox( 'from_lk', 1, \Input::old( 'from_lk' ) ) !!}
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td colspan="2">
                                <div class="row">
                                    <div class="col-lg-7">
                                        {!! Form::select( 'address_id', [ null => ' -- все -- ' ] + ( $address ? $address->pluck( 'name', 'id' )->toArray() : [] ), \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проблемы', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес работы', 'data-allow-clear' => true ] ) !!}
                                    </div>
                                    <div class="col-lg-5">
                                        {!! Form::text( 'flat', \Input::old( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
                                    </div>
                                </div>
                                <div class="row margin-top-10">
                                    @if ( $regions->count() > 1)
                                        <div class="col-lg-6">
                                            {!! Form::select( 'region_id', [ null => ' -- все -- ' ] + $regions->toArray(), \Input::old( 'region_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Регион' ] ) !!}
                                        </div>
                                        <div class="col-lg-6 text-right">
                                    @else
                                        <div class="col-lg-12 text-right">
                                    @endif
                                        <span class="text-muted small bold">
                                            Фильтр:
                                        </span>
                                        <a href="{{ route( 'tickets.index' ) }}" class="btn btn-sm btn-default tooltips" title="Очистить фильтр">
                                            <i class="icon-close"></i>
                                            Очистить
                                        </a>
                                        <button type="submit" class="btn btn-sm btn-primary tooltips bold" title="Применить фильтр">
                                            <i class="icon-check"></i>
                                            Применить
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </thead>
                    {!! Form::close() !!}
                    {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
                    <tbody id="tickets">
                        <tr id="tickets-new-message" class="hidden">
                            <td colspan="7">
                                <button type="button" class="btn btn-warning btn-block btn-lg" id="tickets-new-show">
                                    Добавлены новые заявки <span class="badge bold" id="tickets-new-count">2</span>
                                </button>
                            </td>
                        </tr>
                    @if ( $ticketManagements->count() )
                        @foreach ( $ticketManagements as $ticketManagement )
                            @include( 'parts.ticket', [ 'ticketManagement' => $ticketManagement ] )
                        @endforeach
                        </tbody>
                        {!! Form::close() !!}
                    @else
                        </tbody>
                    @endif
                </table>

                {{ $ticketManagements->render() }}

                @if ( ! $ticketManagements->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

        @if ( \Auth::user()->can( 'tickets.waybill' ) )
            <div id="controls" style="display: none;">
                {!! Form::open( [ 'url' => route( 'tickets.waybill' ), 'method' => 'get', 'target' => '_blank' ] ) !!}
                {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}
                <button type="submit" class="btn btn-default btn-lg">
                    Распечатать наряд-заказы (<span id="ids-count">0</span>)
                </button>
                {!! Form::close(); !!}
            </div>
        @endif

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <style>
        #controls {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999999;
            background: #2f373e;
            padding: 15px 0;
            text-align: center;
        }
        .alert, .mt-element-ribbon, .note {
            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -19px;
            top: -19px;
        }
        .color-inherit {
            color: inherit;
        }
        .border-left {
            border-left: 2px solid #b71a00 !important;
        }
        .opacity {
            opacity: 0.5;
        }
        .portlet {
            margin-bottom: 0;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
    <script type="text/javascript">

        function checkTicketCheckbox ()
        {
            var ids = [];
            $( '.ticket-checkbox:checked' ).each( function ()
            {
                ids.push( $( this ).val() );
            });
            $( '#ids-count' ).text( ids.length );
            if ( ids.length )
            {
                $( '#controls' ).fadeIn( 300 );
                $( '#ids' ).val( ids.join( ',' ) );
            }
            else
            {
                $( '#controls' ).fadeOut( 300 );
                $( '#ids' ).val( '' );
            }
        };

        $( document )

            .ready( function ()
            {

                $('.date-picker').datepicker({
                    rtl: App.isRTL(),
                    orientation: "left",
                    autoclose: true
                });

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    allowClear: true,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

                checkTicketCheckbox();

            })

            .on( 'change', '.ticket-checkbox', checkTicketCheckbox );
    </script>
@endsection