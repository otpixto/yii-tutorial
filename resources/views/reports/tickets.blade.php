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
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label' ] ) !!}
            <div class="input-group">
                {!! Form::text( 'date_from', $date_from->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
                <span class="input-group-addon">-</span>
                {!! Form::text( 'date_to', $date_to->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'managements', 'УО', [ 'class' => 'control-label' ] ) !!}
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements" name="managements[]">
                @foreach ( $availableManagements as $management => $arr )
                    <optgroup label="{{ $management }}">
                        @foreach ( $arr as $management_id => $management_name )
                            <option value="{{ $management_id }}" @if ( $managements->find( $management_id ) ) selected="selected" @endif>
                                {{ $management_name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <div id="chartdiv" style="min-height: {{ 50 + ( $managements->count() * 35 ) }}px;" class="hidden-print"></div>

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
            @if ( isset( $data[ $management->id ] ) )
                <tr>
                    <td data-field="name">
                        {{ $management->name }}
                    </td>
                    <td class="text-center info bold">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="total">
                            {{ $data[ $management->id ][ 'total' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'statuses' => 'cancel', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="canceled">
                            {{ $data[ $management->id ][ 'canceled' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'statuses' => 'not_verified', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="not_verified">
                            {{ $data[ $management->id ][ 'not_verified' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'statuses' => 'closed_with_confirm', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="closed_with_confirm">
                            {{ $data[ $management->id ][ 'closed_with_confirm' ] }}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'statuses' => 'closed_without_confirm', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="closed_without_confirm">
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
            @endif
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
                    <a href="{{ route( 'tickets.index', [ 'statuses' => 'cancel', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                        {{ $data['canceled'] }}
                    </a>
                </th>
                <th class="text-center">
                    <a href="{{ route( 'tickets.index', [ 'statuses' => 'not_verified', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                        {{ $data['not_verified'] }}
                    </a>
                </th>
                <th class="text-center">
                    <a href="{{ route( 'tickets.index', [ 'statuses' => 'closed_with_confirm', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                        {{ $data['closed_with_confirm'] }}
                    </a>
                </th>
                <th class="text-center">
                    <a href="{{ route( 'tickets.index', [ 'statuses' => 'closed_without_confirm', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
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

            });

    </script>


@endsection