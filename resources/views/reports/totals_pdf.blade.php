<html>
<head>
    <style>
        .chart {
            min-width: 310px;
            height: 400px;
            max-width: 600px;
            margin: 0 auto;
        }
        @media print {
            html, body {
                width: 100% ! important;
                height: 100% ! important;
                margin: 0 ! important;
                padding: 0 ! important;
            }
            .page-break {
                page-break-after: always;
            }
            .breadcrumbs {
                display: none;
            }
            .container {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <div class="container">

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

                <div class="row">
                    <div class="col-xs-5">
                        <div class="text-left">
                            ПРИНЯТО ВЫЗОВОВ за период <b>{{ $data[ 'calls' ] }}</b>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="text-right">
                            ЗАРЕГИСТРИРОВАНО обращений жителей за период <b>{{ $data[ 'tickets' ] }}</b>
                        </div>
                    </div>
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
                    @foreach ( $data[ 'current' ][ 'statuses' ][ 'uk' ] as $status => $row )
                        <tr>
                            <td class="text-right">
                                {{ $statuses[ $status ] }}
                            </td>
                            <td>
                                {{ $row[ 0 ] }}
                            </td>
                            <td>
                                {{ $row[ 1 ] }}%
                            </td>
                            <td @if ( $row[ 2 ] > 10 ) class="text-danger bold" @else class="text-success bold" @endif>
                                {{ ( $row[ 2 ] > 0 ? '+' : '' ) . $row[ 2 ] }}%
                            </td>
                        </tr>
                    @endforeach
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
                    @foreach ( $data[ 'current' ][ 'statuses' ][ 'rso' ] as $status => $row )
                        <tr>
                            <td class="text-right">
                                {{ $statuses[ $status ] }}
                            </td>
                            <td>
                                {{ $row[ 0 ] }}
                            </td>
                            <td>
                                {{ $row[ 1 ] }}%
                            </td>
                            <td @if ( $row[ 2 ] > 10 ) class="text-danger bold" @else class="text-success bold" @endif>
                                {{ ( $row[ 2 ] > 0 ? '+' : '' ) . $row[ 2 ] }}%
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
                    @php( $i = 0 )
                    @foreach ( $data[ 'current' ][ 'types' ] as $type => $row )
                        <tr>
                            <td @if ( $i < 5 ) class="text-danger bold" @endif>
                                <span data-field="category">
                                    {{ $type }}
                                </span>
                            </td>
                            <td @if ( $i < 5 ) class="text-danger bold" @endif>
                                {{ $row[ 0 ] }}
                            </td>
                            <td @if ( $i < 5 ) class="text-danger bold" @endif>
                                <span data-field="percent">
                                    {{ $row[ 1 ] }}
                                </span>
                                %
                            </td>
                            <td @if ( $row[ 2 ] > 10 ) class="text-danger bold" @else class="text-success bold" @endif>
                                {{ ( $row[ 2 ] > 0 ? '+' : '' ) . $row[ 2 ] }}%
                            </td>
                        </tr>
                        @php ( $i ++ )
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

                <div id="types-pie" class="chart"></div>


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
                    Сводка обращений по управляющим организациям
                </div>

                <table class="table table-bordered" id="table-managements">
                    <thead>
                        <tr>
                            <th class="text-center" rowspan="2">
                                Название
                            </th>
                            <th class="text-center">
                                Принято обращений за период
                            </th>
                            <th class="text-center" colspan="2">
                                Выполнено  за период
                            </th>
                            <th class="text-center" colspan="2">
                                Выполнено с нарушением сроков
                            </th>
                            <th class="text-center" colspan="2">
                                Просрочено (не выполнено)
                            </th>
                            <th class="text-center" colspan="2">
                                На доработку
                            </th>
                            <th class="text-center" rowspan="2">
                                Средняя оценка заявителем качества работ
                                (от 1 до 5)
                            </th>
                            <th class="text-center" rowspan="2">
                                Рейтинговый балл
                            </th>
                        </tr>
                        <tr>
                            <th class="text-center">
                                Кол-во
                            </th>
                            <th class="text-center">
                                Кол-во
                            </th>
                            <th class="text-center">
                                %
                            </th>
                            <th class="text-center">
                                Кол-во
                            </th>
                            <th class="text-center">
                                %
                            </th>
                            <th class="text-center">
                                Кол-во
                            </th>
                            <th class="text-center">
                                %
                            </th>
                            <th class="text-center">
                                Кол-во
                            </th>
                            <th class="text-center">
                                %
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $data[ 'current' ][ 'parents' ] as $parentManagement => $row )
                        <tr @if ( $row[ 'rating' ] >= 40 ) class="bg-success bold" @elseif ( $row[ 'rating' ] >= 30 ) class="bg-warning bold" @else class="bg-danger bold" @endif>
                            <td>
                                <span data-field="management">
                                    {{ $parentManagement }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ $row[ 'total' ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'completed' ][ 0 ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'completed' ][ 1 ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'expired' ][ 0 ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'expired' ][ 1 ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'not_completed' ][ 0 ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'not_completed' ][ 1 ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'in_process' ][ 0 ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'statuses' ][ 'in_process' ][ 1 ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'avg_rate' ] }}
                            </td>
                            <td class="text-center">
                                <span data-field="rating">
                                    {{ $row[ 'rating' ] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div id="managements-chart" class="chart"></div>


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
                    Сводка обращений по содержанию городских территорий и вывозу ТКО
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">
                                Название
                            </th>
                            <th class="text-center">
                                Принято за период
                            </th>
                            <th class="text-center">
                                Выполнено  за период:<br />
                                количество
                            </th>
                            <th class="text-center">
                                Выполнено за период:<br />
                                %
                            </th>
                            <th class="text-center">
                                Просрочено <br />
                                %
                            </th>
                            <th class="text-center">
                                Средняя оценка заявителем качества работ (от 1 до 5)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $data[ 'current' ][ 'managements' ] as $management => $row )
                        <tr>
                            <td>
                                {{ $management }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'total' ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'completed' ] }}
                            </td>
                            <td class="text-center">
                                {{ $row[ 'completed_percent' ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'expired_percent' ] }}
                                %
                            </td>
                            <td class="text-center">
                                {{ $row[ 'avg_rate' ] }}
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
                    Сводная информация по отключениям ресурсов за период
                </div>

                <div id="works-chart" class="chart"></div>

            @else
                <div class="text-center">
                    <h3>Данные загружаются... Обновите страницу через 5 минут.</h3>
                    <div class="report-loading"></div>
                    <div class="margin-top-15">
                        <a class="btn btn-info btn-lg" href="">
                            Обновить
                        </a>
                    </div>
                </div>
            @endif

        @endif

    </div>

    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script type="text/javascript">

        $( document )

            .ready( function ()
            {
                $( '.report-loading' ).loading();

                        @if ( $data )

                var works = <?php echo json_encode( $data[ 'works' ] ); ?>;
                var categories = [];
                var series = [
                    {
                        name: 'Плановые',
                        data: [],
                        color: '#66FF00'
                    },
                    {
                        name: 'Неплановые',
                        data: [],
                        color: '#CC0000'
                    }
                ];
                $.each( works, function ( category, arr )
                {
                    categories.push( category );
                    series[ 0 ].data.push( arr[ 0 ] );
                    series[ 1 ].data.push( arr[ 1 ] );
                });

                Highcharts.chart( 'works-chart', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: null
                    },
                    subtitle: {
                        text: null
                    },
                    xAxis: {
                        categories: categories,
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Количество отключений'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: series
                });

                var data = [];
                var categories = [];
                var series = [
                    {
                        name: 'Рейтинг',
                        data: [],
                    }
                ];

                $( '#table-managements tbody tr' ).each( function ()
                {
                    var category = $.trim( $( this ).find( '[data-field="management"]' ).text() );
                    var rating = Number( $.trim( $( this ).find( '[data-field="rating"]' ).text() ) );
                    if ( rating >= 0 )
                    {
                        categories.push( category );
                        series[ 0 ].data.push( rating );
                    }
                });

                Highcharts.chart( 'managements-chart', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: null
                    },
                    subtitle: {
                        text: null
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Рейтинг',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    series: series
                });

                var data = [];

                $( '#table-categories tbody tr' ).each( function ()
                {
                    data.push({
                        name: $.trim( $( this ).find( '[data-field="category"]' ).text() ),
                        y: Number( $.trim( $( this ).find( '[data-field="percent"]' ).text() ) ),
                    });
                });

                // Radialize the colors
                Highcharts.setOptions({
                    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
                        return {
                            radialGradient: {
                                cx: 0.5,
                                cy: 0.3,
                                r: 0.7
                            },
                            stops: [
                                [0, color],
                                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                            ]
                        };
                    })
                });

                // Build the chart
                Highcharts.chart( 'types-pie', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: null
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                },
                                connectorColor: 'silver'
                            }
                        }
                    },
                    series: [{
                        name: 'Share',
                        data: data
                    }]
                });
                @endif

            })
            .on( 'change', '#report', function ()
            {
                var val = $( this ).val().split( '|' );
                $( '#date_from' ).val( val[0] );
                $( '#date_to' ).val( val[1] );
            });

    </script>

</body>
</html>