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

    {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading hidden-print margin-bottom-15' ] ) !!}

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
            {!! Form::label( 'managements_ids', 'УО', [ 'class' => 'control-label' ] ) !!}
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements_ids" name="managements_ids[]">
                @foreach ( $availableManagements as $management => $arr )
                    <optgroup label="{{ $management }}">
                        @foreach ( $arr as $management_id => $management_name )
                            <option value="{{ $management_id }}" @if ( in_array( $management_id, $managements_ids ) ) selected="selected" @endif>
                                {{ $management_name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ( $managements )

        <div id="chartdiv" style="min-height: {{ 50 + ( $managements->count() * 35 ) }}px;" class="hidden-print"></div>

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
            @foreach ( $managements as $management )
                <tr>
                    <td data-field="name">
                        {{ $management->name }}
                    </td>
                    <td class="text-center info bold">
                        {{ $data[ $management->id ][ 'total' ] }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'rate' => 1, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="1">
                            {{ $data[ $management->id ][ 'rate-1' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'rate' => 2, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="2">
                            {{ $data[ $management->id ][ 'rate-2' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'rate' => 3, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="3">
                            {{ $data[ $management->id ][ 'rate-3' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'rate' => 4, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="4">
                            {{ $data[ $management->id ][ 'rate-4' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'rate' => 5, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="5">
                            {{ $data[ $management->id ][ 'rate-5' ] }}
                        </a>
                    </td>
                    <td data-field="average" class="text-right info bold">
                        {{ $data[ $management->id ][ 'average' ] }}
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
                        {{ $data[ 'total' ] }}
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $managements->implode( 'id', ',' ), 'rate' => 1, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $data[ 'rate-1' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $managements->implode( 'id', ',' ), 'rate' => 2, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $data[ 'rate-2' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $managements->implode( 'id', ',' ), 'rate' => 3, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $data[ 'rate-3' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $managements->implode( 'id', ',' ), 'rate' => 4, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $data[ 'rate-4' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $managements->implode( 'id', ',' ), 'rate' => 5, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $data[ 'rate-5' ] }}
                        </a>
                    </th>
                    <th class="text-right warning">
                        {{ $data[ 'average' ] }}
                    </th>
                </tr>
            </tfoot>
        </table>

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
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

    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
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

            .ready( function ()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

                var dataProvider = [];

                $( '#data tbody tr' ).each( function ()
                {

                    dataProvider.push({
                        'name': $.trim( $( this ).find( '[data-field="name"]' ).text() ),
                        'average': $.trim( $( this ).find( '[data-field="average"]' ).text() ),
                    });

                });

                var chart = AmCharts.makeChart("chartdiv", {
                    "dataProvider": dataProvider,
                    "graphs": [
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

                $( '.mt-multiselect' ).multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on( 'change', '#provider_id', function ()
            {
                $( this ).closest( 'form' ).submit();
            });

    </script>


@endsection