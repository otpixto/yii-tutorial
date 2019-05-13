@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="container">

        {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading hidden-print margin-bottom-15' ] ) !!}
        {!! Form::hidden( 'report', 1 ) !!}
        <div class="row margin-bottom-15">
            <div class="col-md-6 col-md-offset-3">
                {!! Form::label( null, 'Отчет', [ 'class' => 'control-label', 'id' => 'report' ] ) !!}
                <select class="form-control" id="report">
                    <option value="">-</option>
                    @foreach ( $reports as $item )
                        <option value="{{ $item->date_from->format( 'Y-m-d\TH:i' ) . '|' . $item->date_to->format( 'Y-m-d\TH:i' ) }}">
                            Период {{ $item->date_from->format( 'd.m.y H:i' ) . ' - ' . $item->date_to->format( 'd.m.y H:i' ) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
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
            <div class="col-xs-offset-3 col-xs-3">
                {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
            </div>
        </div>
        {!! Form::close() !!}

        @if ( $report )

            @if ( $data )

                <div class="h3 text-center text-primary bold">
                    СВОДНЫЙ ОТЧЕТ ОБРАЩЕНИЙ ЖИТЕЛЕЙ г.о. ЖУКОВСКИЙ ПО ВОПРОСАМ ЖКХ
                </div>

                <div class="row visible-print">
                    <div class="col-xs-6">
                        <div class="h4 text-primary bold">
                            ЕДС ЖКХ Жуковский
                        </div>
                    </div>
                    <div class="col-xs-6 text-right">
                        <div class="h4 text-primary bold">
                            ПЕРИОД: {{ $date_from->format( 'd.m.y' ) }}-{{ $date_to->format( 'd.m.y' ) }}
                        </div>
                    </div>
                </div>

                <div class="text-justify">
                    ПРИНЯТО ВЫЗОВОВ  за период	{{ $data[ 'calls' ] }}  ЗАРЕГИСТРИРОВАНО  обращений жителей	за период   {{ $data[ 'tickets' ] }}
                </div>

                <div class="h4 text-center text-primary bold margin-top-30">
                    Сводка по обращениям в адрес Управляющих Организаций
                </div>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-right text-primary">
                            СТАТУСЫ ЗАЯВОК
                        </th>
                        <th class="text-primary">
                            Кол-во
                        </th>
                        <th class="text-primary">
                            %
                        </th>
                        <th class="text-primary">
                            +(-) к пред. периоду %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-right">
                            Зарегистрировано
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Выполнено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            В работе
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Отклонено/Отменено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Отложены
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Просрочено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'uk' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'uk' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'uk' ][ 2 ] }}%
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="h4 text-center text-primary bold margin-top-30">
                    Сводка по обращениям в адрес РСО, служб благоустройства и других участников информационного обмена
                </div>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-right text-primary">
                            СТАТУСЫ ЗАЯВОК
                        </th>
                        <th class="text-primary">
                            Кол-во
                        </th>
                        <th class="text-primary">
                            %
                        </th>
                        <th class="text-primary">
                            +(-) к пред. периоду %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-right">
                            Зарегистрировано
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'total' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Выполнено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'completed' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            В работе
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'in_process' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Отклонено/Отменено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'cancel' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Отложены
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'waiting' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            Просрочено
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'rso' ][ 0 ] }}
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'rso' ][ 1 ] }}%
                        </td>
                        <td>
                            {{ $data[ 'current' ][ 'statuses' ][ 'expired' ][ 'rso' ][ 2 ] }}%
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="page-break"></div>

                <div class="row visible-print">
                    <div class="col-xs-6">
                        <div class="h4 text-primary bold">
                            ЕДС ЖКХ Жуковский
                        </div>
                    </div>
                    <div class="col-xs-6 text-right">
                        <div class="h4 text-primary bold">
                            ПЕРИОД: {{ $date_from->format( 'd.m.y' ) }}-{{ $date_to->format( 'd.m.y' ) }}
                        </div>
                    </div>
                </div>

                <div class="h4 text-center text-primary bold margin-top-30">
                    РАСПРЕДЕЛЕНИЕ ОБРАЩЕНИЙ ПО ТИПАМ ПРОБЛЕМ
                </div>

                <table class="table table-bordered" id="table-categories">
                    <thead>
                    <tr>
                        <th>
                            Категория проблем
                        </th>
                        <th class="text-center">
                            Поступило обращений, количество
                        </th>
                        <th class="text-center">
                            Процент от общего количества
                        </th>
                        <th class="text-center">
                            +(-) к пред. периоду, %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $data[ 'current' ][ 'types' ] as $type => $row )
                        <tr>
                            <td>
                            <span data-field="category">
                                {{ $type }}
                            </span>
                            </td>
                            <td>
                                {{ $row[ 0 ] }}
                            </td>
                            <td>
                            <span data-field="percent">
                                {{ $row[ 1 ] }}
                            </span>
                                %
                            </td>
                            <td>
                                {{ $row[ 2 ] }}%
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="page-break"></div>

                <div class="row visible-print">
                    <div class="col-xs-6">
                        <div class="h4 text-primary bold">
                            ЕДС ЖКХ Жуковский
                        </div>
                    </div>
                    <div class="col-xs-6 text-right">
                        <div class="h4 text-primary bold">
                            ПЕРИОД: {{ $date_from->format( 'd.m.y' ) }}-{{ $date_to->format( 'd.m.y' ) }}
                        </div>
                    </div>
                </div>

                <div class="h4 text-center text-primary bold margin-top-30">
                    РАСПРЕДЕЛЕНИЕ ОБРАЩЕНИЙ ПО ТИПАМ ПРОБЛЕМ
                </div>

                <div id="pie"></div>

            @else
                <div class="text-center">
                    <h3>Данные загружаются... Обновите страницу через 5 минут.</h3>
                    <div class="report-loading"></div>
                </div>
            @endif

        @endif

    </div>

@endsection

@section( 'css' )
    <style>
        #pie {
            height: 800px;
            width: 1000px;
            margin: 0 auto;
        }
        @media print {
            .page-break {
                page-break-after: always;
            }
            .breadcrumbs {
                display: none;
            }
        }
    </style>
@endsection

@section( 'js' )
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart()
        {

            if ( ! $( '#table-categories tbody tr' ).length ) return;

            var data = [
                [
                    'Категория', '%'
                ]
            ];

            $( '#table-categories tbody tr' ).each( function ()
            {
                data.push([
                    $.trim( $(this).find('[data-field="category"]').text() ), Number( $.trim($(this).find('[data-field="percent"]').text()) )
                ]);
            });

            var dataTable = google.visualization.arrayToDataTable(data);

            var options = {
                is3D: true,
            };

            var chart = new google.visualization.PieChart(document.getElementById('pie'));
            chart.draw(dataTable, options);
        };

        $( document )
            .ready( function ()
            {
                $( '.report-loading' ).loading();
            })
            .on( 'change', '#report', function ()
            {
                var val = $( this ).val().split( '|' );
                $( '#date_from' ).val( val[0] );
                $( '#date_to' ).val( val[1] );
            });

    </script>
@endsection