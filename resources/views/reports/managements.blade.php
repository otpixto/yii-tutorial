@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            <div class="col-xs-6">
                {!! Form::text( 'date_from', $date_from, [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
            <div class="col-xs-6">
                {!! Form::text( 'date_to', $date_to, [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
        </div>
        <div class="col-md-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <div id="chartdiv" style="min-height: 500px;"></div>

    <table class="table table-hover table-striped sortable" id="data">
        <thead>
        <tr>
            <th>
                Нименование ЭО
            </th>
            <th class="text-center">
                Всего заявок
            </th>
            <th class="text-center">
                Выполнено
            </th>
            <th class="text-center">
                Отменено
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
                <td data-field="canceled" class="text-center">
                    {{ $r['canceled'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
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
                        'completed': $.trim( $( this ).find( '[data-field="completed"]' ).text() ),
                        'canceled': $.trim( $( this ).find( '[data-field="canceled"]' ).text() )
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
                        },
                        {
                            "balloonText": "Отменено: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "canceled",
                            "lineAlpha": 0.2,
                            "title": "Отменено",
                            "type": "column",
                            "valueField": "canceled"
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