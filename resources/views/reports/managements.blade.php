@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <p class="visible-print">
        за период с {{ $date_from }} по {{ $date_to }}
    </p>

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal hidden-print' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-3">
            {!! Form::text( 'date_from', $date_from, [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::text( 'date_to', $date_to, [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
            @if ( \Auth::user()->admin || \Auth::user()->can( 'reports.export' ) )
                {!! Form::submit( 'Выгрузить', [ 'class' => 'btn btn-info', 'name' => 'export' ] ) !!}
            @endif
        </div>
    </div>
    {!! Form::close() !!}

    @if ( $data[ 'total' ] )

        <div id="chartdiv" style="min-height: {{ $managements->count() * 50 }}px;" class="hidden-print"></div>

        <table class="table table-striped sortable" id="data">
            <thead>
                <tr>
                    <th rowspan="3">
                        Нименование УО
                    </th>
                    <th class="text-center info bold" rowspan="2">
                        Поступило заявок
                    </th>
                    <th class="text-center" colspan="5">
                        Закрыто заявок
                    </th>
                </tr>
                <tr>
                    <th class="text-center">
                        Отменено Заявителем
                    </th>
                    <th class="text-center">
                        Проблема не подтверждена
                    </th>
                    <th class="text-center">
                        С подтверждением
                    </th>
                    <th class="text-center">
                        Без подтверждения
                    </th>
                    <th class="text-center info bold">
                        Всего
                    </th>
                    <th colspan="2" class="text-center">
                        Процент выполнения
                    </th>
                </tr>
            </thead>
            <tbody>
            @foreach ( $managements as $management )
                <tr>
                    <td data-field="name">
                        {{ $management->name }}
                    </td>
                    <td class="text-center info bold">
                        <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'period_from' => $date_from, 'period_to' => $date_to ] ) }}" data-field="total">
                            {{ $data[ $management->id ][ 'total' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'status_code' => 'cancel', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}" data-field="canceled">
                            {{ $data[ $management->id ][ 'canceled' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'status_code' => 'not_verified', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}" data-field="not_verified">
                            {{ $data[ $management->id ][ 'not_verified' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'status_code' => 'closed_with_confirm', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}" data-field="closed_with_confirm">
                            {{ $data[ $management->id ][ 'closed_with_confirm' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'status_code' => 'closed_without_confirm', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}" data-field="closed_without_confirm">
                            {{ $data[ $management->id ][ 'closed_without_confirm' ] }}
                        </a>
                    </td>
                    <td data-field="closed" class="text-center info bold">
                        {{ $data[ $management->id ][ 'closed' ] }}
                    </td>
                    <td class="text-right" data-field="percent" style="width: 40px;">
                        {{ $data[ $management->id ][ 'total' ] ? ceil( $data[ $management->id ][ 'closed' ] * 100 / $data[ $management->id ][ 'total' ] ) : 0 }}%
                    </td>
                    <td class="hidden-print" style="width: 15%;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $data[ $management->id ][ 'total' ] ? ceil( $data[ $management->id ][ 'closed' ] * 100 / $data[ $management->id ][ 'total' ] ) : 0 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $data[ $management->id ][ 'total' ] ? ceil( $data[ $management->id ][ 'closed' ] * 100 / $data[ $management->id ][ 'total' ] ) : 0 }}%">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">
                        Всего:
                    </th>
                    <th class="text-center warning">
                        {{ $data['total'] }}
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'status_code' => 'cancel', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}">
                            {{ $data['canceled'] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'status_code' => 'not_verified', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}">
                            {{ $data['not_verified'] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'status_code' => 'closed_with_confirm', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}">
                            {{ $data['closed_with_confirm'] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'status_code' => 'closed_without_confirm', 'period_from' => $date_from, 'period_to' => $date_to ] ) }}">
                            {{ $data['closed_without_confirm'] }}
                        </a>
                    </th>
                    <th class="text-center warning">
                        {{ $data['closed'] }}
                    </th>
                    <th class="text-right">
                        {{ $data['total'] ? ceil( $data['closed'] * 100 / $data['total'] ) : 0 }}%
                    </th>
                    <th class="hidden-print">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $data['total'] ? ceil( $data['closed'] * 100 / $data['total'] ) : 0 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $data['total'] ? ceil( $data['closed'] * 100 / $data['total'] ) : 0 }}%">
                            </div>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>

    @else
        @include( 'parts.error', [ 'error' => 'По Вашему запросу ничего не найдено' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        .progress {
            margin-bottom: 0 !important;
        }
        .table tfoot th, .table tfoot td {
            padding: 8px !important;
        }
    </style>
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/amcharts/amcharts/amcharts.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/serial.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/pie.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/radar.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/themes/light.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/themes/patterns.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amcharts/themes/chalk.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/ammap/ammap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/ammap/maps/js/worldLow.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/amcharts/amstockcharts/amstock.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

                var dataProvider = [];

                $( '#data tbody tr' ).each( function ()
                {

                    dataProvider.push({
                        'name': $.trim( $( this ).find( '[data-field="name"]' ).text() ),
                        'total': $.trim( $( this ).find( '[data-field="total"]' ).text() ),
                        'closed': $.trim( $( this ).find( '[data-field="closed"]' ).text() ),
                    });

                });

                var chart = AmCharts.makeChart("chartdiv", {
                    "dataProvider": dataProvider,
                    "graphs": [
                        {
                            "balloonText": "Поступило: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "total",
                            "lineAlpha": 0.2,
                            "title": "Всего",
                            "type": "column",
                            "valueField": "total"
                        },
                        {
                            "balloonText": "Закрыто: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "closed",
                            "lineAlpha": 0.2,
                            "title": "Закрыто",
                            "type": "column",
                            "valueField": "closed"
                        }
                    ],
                    "type": "serial",
                    "theme": "light",
                    "categoryField": "name",
                    "rotate": true,
                    "startDuration": 1,
                    "categoryAxis": {
                        "gridPosition": "start",
                        "position": "left"
                    },
                    "trendLines": [],
                    "guides": [],
                    "valueAxes": [
                        {
                            "id": "ValueAxis-1",
                            "position": "top",
                            "axisAlpha": 0
                        }
                    ],
                    "allLabels": [],
                    "balloon": {},
                    "titles": [],
                });

            });

    </script>


@endsection