@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <p class="visible-print">
        за период с {{ $date_from->format( 'd.m.Y' ) }} по {{ $date_to->format( 'd.m.Y' ) }}
    </p>

    {!! Form::open( [ 'method' => 'get', 'class' => 'hidden-print submit-loading' ] ) !!}

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label' ] ) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    от
                </span>
                <input class="form-control" name="date_from" type="datetime-local" value="{{ $date_from->format( 'Y-m-d\TH:i' ) }}" id="date_from" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
                <span class="input-group-addon">
                    до
                </span>
                <input class="form-control" name="date_to" type="datetime-local" value="{{ $date_to->format( 'Y-m-d\TH:i' ) }}" id="date_to" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
            </div>
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'operator', 'Оператор', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'operator_id', [ null => ' -- ВСЕ -- ' ] + $availableOperators, $operator_id, [ 'class' => 'form-control select2' ] ) !!}
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ( count( $data ) )

        <div id="chartdiv" style="min-height: 500px;" class="hidden-print"></div>

        <table class="table table-striped sortable" id="data">
            <thead>
                <tr>
                    <th rowspan="2">
                        Дата | время
                    </th>
                    <th class="text-center bold" colspan="2">
                        Входящие звонки
                    </th>
                    <th class="text-center bold" colspan="2">
                        Исходящие звонки
                    </th>
                    <th class="text-center info bold" rowspan="2">
                        Создано заявок
                    </th>
                </tr>
                <tr>
                    <th class="text-center info bold">
                        Кол-во
                    </th>
                    <th class="text-center">
                        Длительность
                    </th>
                    <th class="text-center info bold">
                        Кол-во
                    </th>
                    <th class="text-center">
                        Длительность
                    </th>
                </tr>
            </thead>
            <tbody>
            @foreach ( $data as $date => $arr )
                <tr @if ( ! $arr[ 'incoming' ][ 'calls' ] && ! $arr[ 'outgoing' ][ 'calls' ] && ! $arr[ 'tickets' ] ) class="text-muted" @endif>
                    <td data-field="date">
                        {{ $date }}
                    </td>
                    <td class="text-center info bold" data-field="incoming-calls">
                        {{ $arr[ 'incoming' ][ 'calls' ] }}
                    </td>
                    <td class="text-center" data-field="incoming-duration" data-value="{{ $arr[ 'incoming' ][ 'duration' ] }}">
                        {{ gmdate( 'H:i:s', $arr[ 'incoming' ][ 'duration' ] ) }}
                    </td>
                    <td class="text-center info bold" data-field="outgoing-calls">
                        {{ $arr[ 'outgoing' ][ 'calls' ] }}
                    </td>
                    <td class="text-center" data-field="outgoing-duration" data-value="{{ $arr[ 'outgoing' ][ 'duration' ] }}">
                        {{ gmdate( 'H:i:s', $arr[ 'outgoing' ][ 'duration' ] ) }}
                    </td>
                    <td class="text-center info bold" data-field="tickets">
                        {{ $arr[ 'tickets' ] }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">
                        Итого
                    </th>
                    <th class="bold text-center warning">
                        {{ $totals[ 'incoming' ][ 'calls' ] }}
                    </th>
                    <th class="bold text-center">
                        @if ( $totals[ 'incoming' ][ 'duration' ] > 86400 )
                            {{ gmdate( 'dд. H:i:s', $totals[ 'incoming' ][ 'duration' ] - 86400 ) }}
                        @else
                            {{ gmdate( 'H:i:s', $totals[ 'incoming' ][ 'duration' ] ) }}
                        @endif
                    </th>
                    <th class="bold text-center warning">
                        {{ $totals[ 'outgoing' ][ 'calls' ] }}
                    </th>
                    <th class="bold text-center">
                        @if ( $totals[ 'outgoing' ][ 'duration' ] > 86400 )
                            {{ gmdate( 'dд. H:i:s', $totals[ 'outgoing' ][ 'duration' ] - 86400 ) }}
                        @else
                            {{ gmdate( 'H:i:s', $totals[ 'outgoing' ][ 'duration' ] ) }}
                        @endif
                    </th>
                    <th class="text-center warning bold">
                        {{ $totals[ 'tickets' ] }}
                    </th>
                </tr>
            </tfoot>
        </table>

    @else
        @include( 'parts.error', [ 'error' => 'По Вашему запросу ничего не найдено' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

                $( '.datetimepicker' ).datetimepicker({
                    isRTL: App.isRTL(),
                    format: "dd.mm.yyyy hh:ii",
                    autoclose: true,
                    fontAwesome: true,
                    todayBtn: true
                });

                var dataProvider = [];

                $( '#data tbody tr' ).each( function ()
                {

                    dataProvider.push({
                        'date': $.trim( $( this ).find( '[data-field="date"]' ).text() ),
                        'incoming': $.trim( $( this ).find( '[data-field="incoming-calls"]' ).text() ),
                        'outgoing': $.trim( $( this ).find( '[data-field="outgoing-calls"]' ).text() ),
                        'tickets': $.trim( $( this ).find( '[data-field="tickets"]' ).text() ),
                    });

                });

                var chart = AmCharts.makeChart("chartdiv", {
                    "type": "serial",
                    "theme": "light",
                    "legend": {
                        "equalWidths": false,
                        "useGraphSettings": true,
                        "valueAlign": "left",
                        "valueWidth": 120
                    },
                    "dataProvider": dataProvider,
                    "valueAxes": [
                        {
                            "id": "count",
                            "axisAlpha": 0,
                            "gridAlpha": 0,
                            "position": "left",
                            "title": "Количество"
                        }
                    ],
                    "graphs": [
                        {
                            "balloonText": "[[value]]",
                            "fillAlphas": 0.7,
                            "legendPeriodValueText": "Всего: [[value.sum]]",
                            "legendValueText": "[[value]]",
                            "title": "Входящие",
                            "type": "column",
                            "valueField": "incoming",
                            "valueAxis": "count"
                        },
                        {
                            "balloonText": "[[value]]",
                            "fillAlphas": 0.7,
                            "legendPeriodValueText": "Всего: [[value.sum]]",
                            "legendValueText": "[[value]]",
                            "title": "Исходящие",
                            "type": "column",
                            "valueField": "outgoing",
                            "valueAxis": "count"
                        },
                        {
                            "balloonText": "[[value]]",
                            "fillAlphas": 0.7,
                            "legendPeriodValueText": "Всего: [[value.sum]]",
                            "legendValueText": "[[value]]",
                            "title": "Заявки",
                            "type": "column",
                            "valueField": "tickets",
                            "valueAxis": "count"
                        }
                    ],
                    "chartCursor": {
                        "categoryBalloonDateFormat": "DD.MM.YYYY",
                        "cursorAlpha": 0.1,
                        "cursorColor":"#000000",
                        "fullWidth":true,
                        "valueBalloonsEnabled": false,
                        "zoomable": false
                    },
                    "dataDateFormat": "DD.MM.YYYY",
                    "categoryField": "date",
                    "categoryAxis": {
                        "gridPosition": "start",
                        "labelRotation": dataProvider.length > 10 ? 60 : 0
                    },
                    "export": {
                        "enabled": true
                    }
                });

            });

    </script>


@endsection