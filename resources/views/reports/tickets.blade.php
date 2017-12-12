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
            @if ( \Auth::user()->can( 'reports.export' ) )
                {!! Form::submit( 'Выгрузить', [ 'class' => 'btn btn-info', 'name' => 'export' ] ) !!}
            @endif
        </div>
    </div>
    {!! Form::close() !!}

    @if ( count( $data ) )

        <div id="chartdiv" style="min-height: 500px;" class="hidden-print"></div>

        <table class="table table-striped sortable" id="data">
            <thead>
                <tr>
                    <th rowspan="3">
                        Дата \ время
                    </th>
                    <th class="text-center info bold">
                        Поступило заявок
                    </th>
                </tr>
            </thead>
            <tbody>
            @foreach ( $data as $date => $count )
                <tr @if ( ! $count ) class="text-muted" @endif>
                    <td data-field="date">
                        {{ $date }}
                    </td>
                    <td class="text-center info bold" data-field="count">
                        {{ $count }}
                    </td>
                </tr>
            @endforeach
            </tbody>
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
                        'date': $.trim( $( this ).find( '[data-field="date"]' ).text() ),
                        'count': $.trim( $( this ).find( '[data-field="count"]' ).text() )
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
                            "title": "Количество заявок"
                        }
                    ],
                    "graphs": [
                        {
                            "balloonText": "[[value]]",
                            "fillAlphas": 0.7,
                            "legendPeriodValueText": "Всего: [[value.sum]]",
                            "legendValueText": "[[value]]",
                            "title": "Количество заявок",
                            "type": "column",
                            "valueField": "count",
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
                        "labelRotation": dataProvider.length > 24 ? 60 : 0
                    },
                    "export": {
                        "enabled": true
                    }
                });

            });

    </script>


@endsection