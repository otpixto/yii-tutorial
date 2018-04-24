@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="visible-print title">
        Статистический отчет по категориям за период с {{ $date_from->format( 'd.m.Y' ) }} по {{ $date_to->format( 'd.m.Y' ) }}
    </div>

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal hidden-print' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-3">
            {!! Form::text( 'date_from', $date_from->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::text( 'date_to', $date_to->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <table class="table table-striped sortable" id="table-categories">
        <thead>
            <tr>
                <th>
                    Категория проблем
                </th>
                <th class="text-center info bold">
                    Поступило заявок, кол-во
                </th>
                <th class="text-center">
                    Процент от общего количества
                </th>
                <th class="text-center info bold">
                    Закрыто заявок, кол-во
                </th>
                <th class="text-center">
                    Процент закрытых заявок
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach ( $categories as $category )
            <tr>
                <td>
                    <span data-field="category">
                        {{ $category->name }}
                    </span>
                </td>
                <td class="text-center info bold">
                    {{ $data[ 'categories' ][ $category->id ][ 'total' ] }}
                </td>
                <td class="text-center">
                    <span data-field="percent">
                        {{ $data[ 'categories' ][ $category->id ][ 'percent_total' ] }}
                    </span>
                    %
                </td>
                <td class="text-center info bold">
                    {{ $data[ 'categories' ][ $category->id ][ 'closed' ] }}
                </td>
                <td class="text-center">
                    {{ $data[ 'categories' ][ $category->id ][ 'percent' ] }}%
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">
                    Всего:
                </th>
                <th class="text-center warning bold">
                    {{ $data[ 'total' ] }}
                </th>
                <th class="text-center">
                    100%
                </th>
                <th class="text-center warning bold">
                    {{ $data[ 'closed' ] }}
                </th>
                <th class="text-center">
                    {{ $data[ 'percent' ] }}%
                </th>
            </tr>
        </tfoot>
    </table>

    @if ( $categories_count )
        <div id="pie-categories" style="min-height: {{ 100 + $categories_count * 35 }}px;" class="hidden-print"></div>
    @endif

    <div class="pagebreak"></div>

    <div class="table-responsive">
        <table class="table table-striped sortable" id="table-managements">
            <thead>
            <tr>
                <th>
                    Тип проблемы \ Нименование УО
                </th>
                @foreach ( $managements as $management )
                    <th class="text-center">
                        {{ $management->name }}
                    </th>
                @endforeach
                <th class="text-center info bold">
                    Всего
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ( $categories as $category )
                <tr>
                    <td>
                        {{ $category->name }}
                    </td>
                    @foreach ( $managements as $management )
                        <td class="text-center">
                            @if ( isset( $data[ 'data' ][ $category->id ], $data[ 'data' ][ $category->id ][ $management->id ] ) )
                                {{ $data[ 'data' ][ $category->id ][ $management->id ][ 'closed' ] }}
                                /
                                <a href="{{ route( 'tickets.index', [ 'management_id' => $management->id, 'type' => 'category-' . $category->id, 'period_from' => $date_from->format( 'd.m.Y' ), 'period_to' => $date_to->format( 'd.m.Y' ) ] ) }}" class="bold">
                                    {{ $data[ 'data' ][ $category->id ][ $management->id ][ 'total' ] }}
                                </a>
                            @else
                                0 / 0
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center info bold">
                        @if ( isset( $data[ 'categories' ][ $category->id ] ) )
                            {{ $data[ 'categories' ][ $category->id ][ 'closed' ] }}
                            /
                            <a href="{{ route( 'tickets.index', [ 'type' => 'category-' . $category->id, 'period_from' => $date_from->format( 'd.m.Y' ), 'period_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                                {{ $data[ 'categories' ][ $category->id ][ 'total' ] }}
                            </a>
                        @else
                            0 / 0
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">
                        Всего:
                    </th>
                    @foreach ( $managements as $management )
                        <th class="text-center">
                            {{ $data[ 'managements' ][ $management->id ][ 'closed' ] }}
                            /
                            {{ $data[ 'managements' ][ $management->id ][ 'total' ] }}
                        </th>
                    @endforeach
                    <th class="text-center warning bold">
                        {{ $data[ 'closed' ] }}
                        /
                        {{ $data[ 'total' ] }}
                    </th>
                </tr>
                <tr>
                    <th class="text-right">
                        В % соотношении:
                    </th>
                    @foreach ( $managements as $management )
                        <th class="text-center">
                            <span data-category="{{ $management->name }}" data-percent="{{ $data[ 'managements' ][ $management->id ][ 'percent_total' ] }}">
                                {{ $data[ 'managements' ][ $management->id ][ 'percent_total' ] }}%
                            </span>
                        </th>
                    @endforeach
                    <th class="text-center warning bold">
                        100%
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ( $managements_count )
        <div id="pie-managements" style="min-height: {{ 100 + $managements_count * 35 }}px;" class="hidden-print"></div>
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        @media print {
            td, th {
                font-size: 85% !important;
            }
            .breadcrumbs {
                display: none;
            }
            .title {
                font-weight: bold;
                margin: 10px 0;
            }
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
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

               $( '.select2' ).select2();

                var dataProviderCategories = [];

                $( '#table-categories tbody tr' ).each( function ()
                {

                    dataProviderCategories.push({
                        'category': $.trim( $( this ).find( '[data-field="category"]' ).text() ),
                        'percent': $.trim( $( this ).find( '[data-field="percent"]' ).text() ),
                    });

                });

                AmCharts.makeChart( 'pie-categories', {
                    "type": "pie",
                    "theme": "light",
                    "dataProvider": dataProviderCategories,
                    "valueField": "percent",
                    "titleField": "category",
                    "outlineAlpha": 0.4,
                    "depth3D": 15,
                    "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b>%</span>",
                    "angle": 30,
                    "export": {
                        "enabled": true
                    }
                });

                var dataProviderManagements = [];

                $( '#table-managements [data-category]' ).each( function ()
                {

                    dataProviderManagements.push({
                        'category': $( this ).attr( 'data-category' ),
                        'percent': $( this ).attr( 'data-percent' ),
                    });

                });

                console.log( dataProviderManagements );

                AmCharts.makeChart( 'pie-managements', {
                    "type": "pie",
                    "theme": "light",
                    "dataProvider": dataProviderManagements,
                    "valueField": "percent",
                    "titleField": "category",
                    "outlineAlpha": 0.4,
                    "depth3D": 15,
                    "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b>%</span>",
                    "angle": 30,
                    "export": {
                        "enabled": true
                    }
                });

            });
    </script>
@endsection