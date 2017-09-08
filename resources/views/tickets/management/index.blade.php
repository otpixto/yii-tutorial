@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'tickets.export' )
        <div class="row margin-bottom-15 hidden-print">
            <div class="col-xs-12 text-right">
                <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            </div>
        </div>
    @endcan

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

    <div class="row margin-top-15">
        <div class="col-xs-12">

            {{ $ticketManagements->render() }}

            <table class="table table-striped table-bordered table-hover">
                {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                <thead>
                    <tr class="info">
                        <th>
                             Статус \ Номер заявки \ Оценка
                        </th>
                        <th width="15%">
                            Дата и время создания
                        </th>
                        <th width="30%">
                            Адрес проблемы
                        </th>
                        <th>
                            Категория и тип заявки
                        </th>
                        <th class="hidden-print">
                            &nbsp;
                        </th>
                    </tr>
                    <tr class="info hidden-print">
                        <td>
                            <div class="row">
                                <div class="col-xs-8">
                                    {!! Form::select( 'status_code', [ null => ' -- все -- ' ] + \App\Models\TicketManagement::$statuses, \Input::old( 'status_code' ), [ 'class' => 'form-control select2', 'placeholder' => 'Статус' ] ) !!}
                                </div>
                                <div class="col-xs-4">
                                    {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group date-picker input-daterange" data-date-format="dd.mm.yyyy">
                                {!! Form::text( 'period_from', \Input::old( 'period_from' ), [ 'class' => 'form-control', 'placeholder' => 'ОТ' ] ) !!}
                                <span class="input-group-addon"> - </span>
                                {!! Form::text( 'period_to', \Input::old( 'period_to' ), [ 'class' => 'form-control', 'placeholder' => 'ДО' ] ) !!}
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-xs-8">
                                    {!! Form::select( 'address_id', $address ? $address->pluck( 'name', 'id' )->toArray() : [], \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проблемы', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес работы', 'data-allow-clear' => true ] ) !!}
                                </div>
                                <div class="col-xs-4">
                                    {!! Form::text( 'flat', \Input::old( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
                                </div>
                            </div>
                        </td>
                        <td>
                            {!! Form::select( 'type_id', [ null => ' -- все -- ' ] + $types->pluck( 'name', 'id' )->toArray(), \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип заявки' ] ) !!}
                        </td>
                        <td class="text-right hidden-print">
                            <button type="submit" class="btn btn-primary tooltips" title="Применить фильтр">
                                <i class="fa fa-filter"></i>
                            </button>
                        </td>
                    </tr>
                </thead>
                @if ( $ticketManagements->count() )
                    <tbody>
                        @foreach ( $ticketManagements as $ticketManagement )
                            @include( 'parts.ticket_management', [ 'ticketManagement' => $ticketManagement, 'ticket' => $ticketManagement->ticket ] )
                        @endforeach
                    </tbody>
                @endif
            </table>

            {{ $ticketManagements->render() }}

            @if ( ! $ticketManagements->count() )
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

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
        .alert {
            margin-bottom: 0;
        }
        .mt-element-ribbon {

            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -18px;
            top: -18px;
        }
        .color-inherit {
            color: inherit;
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

            });
    </script>
@endsection