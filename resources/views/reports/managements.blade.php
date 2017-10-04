@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
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

    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                var dataProvider = [];

                $( '#data tbody tr' ).each( function ()
                {

                    dataProvider.push({
                        'name': $.trim( $( this ).find( '[data-field="name"]' ).text() ),
                        'total': $.trim( $( this ).find( '[data-field="total"]' ).text() ),
                        'completed': $.trim( $( this ).find( '[data-field="completed"]' ).text() )
                    });

                });

                var chart = AmCharts.makeChart("chartdiv", {
                    "dataProvider": dataProvider,
                    "graphs": [
                        {
                            "balloonText": "Всего: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "total",
                            "lineAlpha": 0.2,
                            "title": "Всего",
                            "type": "column",
                            "valueField": "total"
                        },
                        {
                            "balloonText": "Выполнено: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "completed",
                            "lineAlpha": 0.2,
                            "title": "Выполнено",
                            "type": "column",
                            "valueField": "completed"
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

@section( 'content' )

    <div id="chartdiv" style="min-height: 500px;"></div>

    <table class="table table-hover table-striped sortable" id="data">
        <thead>
            <tr>
                <th>
                    Нименование ЭО
                </th>
                <th class="text-center">
                    Количество заявок
                </th>
                <th class="text-center">
                    Количество выполненных заявок
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach ( $data as $r )
            <tr>
                <td data-field="name">
                    {{ $r['name'] }}
                </td>
                <td data-field="total" class="text-center">
                    {{ $r['total'] }}
                </td>
                <td data-field="completed" class="text-center">
                    {{ $r['completed'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection