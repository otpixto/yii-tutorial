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

    @if ( $providers->count() > 1 )
        <div class="row margin-bottom-15">
            <div class="col-md-6 col-md-offset-3">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, $provider_id, [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>
    @endif

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
            @if ( count( $data ) && \Auth::user()->can( 'reports.export' ) )
                <a href="{{ Request::fullUrl() }}&export=1" class="btn btn-default">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            @endif
        </div>
    </div>
    {!! Form::close() !!}

    @if ( $data )

        <div id="chartdiv" style="min-height: {{ 50 + ( $managements->count() * 35 ) }}px;" class="hidden-print"></div>

        <table class="table table-striped sortable" id="data">
            <thead>
                <tr>
                    <th>
                        Нименование УО
                    </th>
                    <th class="text-center info bold">
                        Поступило заявок
                    </th>
                    <th class="text-center">
                        Выполнено заявок
                    </th>
                    <th class="text-center">
                        % выполненных заявок
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
                            <a href="{{ route( 'tickets.index', [ 'managements' => $management->id, 'statuses' => 'closed_with_confirm,closed_without_confirm,not_verified,cancel,completed_with_act,completed_without_act,confirmation_operator,confirmation_client,no_contract', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}" data-field="completed">
                                {{ $data[ $management->id ][ 'completed' ] }}
                            </a>
                        </td>
                        <td class="text-center" data-field="percent">
                            {{ $data[ $management->id ][ 'total' ] ? ceil( $data[ $management->id ][ 'completed' ] * 100 / $data[ $management->id ][ 'total' ] ) : 0 }}
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
                        <a href="{{ route( 'tickets.index', [ 'managements' => implode( ',', array_keys( $data ) ), 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $totals[ 'total' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'managements' => implode( ',', array_keys( $data ) ), 'statuses' => 'closed_with_confirm,closed_without_confirm,not_verified,cancel,completed_with_act,completed_without_act,confirmation_operator,confirmation_client,no_contract', 'created_from' => $date_from->format( 'd.m.Y' ), 'created_to' => $date_to->format( 'd.m.Y' ) ] ) }}">
                            {{ $totals[ 'completed' ] }}
                        </a>
                    </th>
                    <th class="text-center">
                        {{ $totals[ 'total' ] ? ceil( $totals[ 'completed' ] * 100 / $totals[ 'total' ] ) : 0 }}
                    </th>
                </tr>
            </tfoot>
        </table>

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <style>
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

    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                var dataProvider = [];

                $( '#data tbody tr' ).each( function ()
                {

                    dataProvider.push({
                        'name': $.trim( $( this ).find( '[data-field="name"]' ).text() ),
                        'total': $.trim( $( this ).find( '[data-field="total"]' ).text() ),
                        'completed': $.trim( $( this ).find( '[data-field="completed"]' ).text() ),
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
                            "balloonText": "Выполнено: [[value]]",
                            "fillAlphas": 0.8,
                            "id": "closed",
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