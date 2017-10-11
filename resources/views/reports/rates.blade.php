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

    @if ( $summary['total'] )

        <div id="chartdiv" style="min-height: 500px;" class="hidden-print"></div>

        <table class="table table-striped sortable" id="data">
            <thead>
                <tr>
                    <th rowspan="3">
                        Нименование ЭО
                    </th>
                    <th class="text-center info bold">
                        Всего оценок
                    </th>
                    <th class="text-center">
                        1 балл
                    </th>
                    <th class="text-center">
                        2 балла
                    </th>
                    <th class="text-center">
                        3 балла
                    </th>
                    <th class="text-center">
                        4 балла
                    </th>
                    <th class="text-center">
                        5 баллов
                    </th>
                    <th class="text-center info bold">
                        Средний балл
                    </th>
                </tr>
            </thead>
            <tbody>
            @foreach ( $data as $r )
                <tr>
                    <td data-field="name">
                        {{ $r['name'] }}
                    </td>
                    <td data-field="total" class="text-center info bold">
                        {{ $r['total'] }}
                    </td>
                    <td data-field="canceled" class="text-center">
                        {{ $r['1'] }}
                    </td>
                    <td data-field="not_verified" class="text-center">
                        {{ $r['2'] }}
                    </td>
                    <td data-field="closed_with_confirm" class="text-center">
                        {{ $r['3'] }}
                    </td>
                    <td data-field="closed_without_confirm" class="text-center">
                        {{ $r['4'] }}
                    </td>
                    <td data-field="closed" class="text-center">
                        {{ $r['5'] }}
                    </td>
                    <td class="text-right info bold" data-field="average">
                        {{ $r['average'] }}
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
                        {{ $summary['total'] }}
                    </th>
                    <th class="text-center">
                        {{ $summary['1'] }}
                    </th>
                    <th class="text-center">
                        {{ $summary['2'] }}
                    </th>
                    <th class="text-center">
                        {{ $summary['3'] }}
                    </th>
                    <th class="text-center">
                        {{ $summary['4'] }}
                    </th>
                    <th class="text-center">
                        {{ $summary['5'] }}
                    </th>
                    <th class="text-right warning">
                        {{ $summary['average'] }}
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
                        'average': $.trim( $( this ).find( '[data-field="average"]' ).text() ),
                    });

                });

                var chart = AmCharts.makeChart("chartdiv", {
                    "dataProvider": dataProvider,
                    "graphs": [
                        {
                            "balloonText": "Всего оценок: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "total",
                            "lineAlpha": 0.2,
                            "title": "Всего оценок",
                            "type": "column",
                            "valueField": "total"
                        },
                        {
                            "balloonText": "Средний балл: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "average",
                            "lineAlpha": 0.2,
                            "title": "Средний балл",
                            "type": "column",
                            "valueField": "average"
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