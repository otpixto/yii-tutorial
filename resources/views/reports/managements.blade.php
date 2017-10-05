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
        </div>
    </div>
    {!! Form::close() !!}

    <div id="chartdiv" style="min-height: 500px;" class="hidden-print"></div>

    <table class="table table-striped sortable" id="data">
        <thead>
            <tr>
                <th rowspan="3">
                    Нименование ЭО
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
                <th>
                    &nbsp;
                </th>
                <th class="hidden-print" style="width: 15%;">
                    &nbsp;
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
                    {{ $r['canceled'] }}
                </td>
                <td data-field="not_verified" class="text-center">
                    {{ $r['not_verified'] }}
                </td>
                <td data-field="closed_with_confirm" class="text-center">
                    {{ $r['closed_with_confirm'] }}
                </td>
                <td data-field="closed_without_confirm" class="text-center">
                    {{ $r['closed_without_confirm'] }}
                </td>
                <td data-field="closed" class="text-center info bold">
                    {{ $r['closed'] }}
                </td>
                <td class="text-right" data-field="percent">
                    {{ ceil( $r['closed'] * 100 / $r['total'] ) }}%
                </td>
                <td class="hidden-print">
                    <div class="progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ ceil( $r['closed'] * 100 / $r['total'] ) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ ceil( $r['closed'] * 100 / $r['total'] ) }}%">
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
                    {{ $summary['total'] }}
                </th>
                <th class="text-center">
                    {{ $summary['canceled'] }}
                </th>
                <th class="text-center">
                    {{ $summary['not_verified'] }}
                </th>
                <th class="text-center">
                    {{ $summary['closed_with_confirm'] }}
                </th>
                <th class="text-center">
                    {{ $summary['closed_without_confirm'] }}
                </th>
                <th class="text-center warning">
                    {{ $summary['closed'] }}
                </th>
                <th class="text-right">
                    {{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}%
                </th>
                <th>
                    <div class="progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}%">
                        </div>
                    </div>
                </th>
            </tr>
        </tfoot>
    </table>

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