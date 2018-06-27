<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8" />
    <title>{{ \App\Classes\Title::render() }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="{{ \Config::get( 'app.name' ) }} - Акт" name="description" />
    <meta content="{{ \Config::get( 'app.author' ) }}" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/assets/pages/css/act.css?v3" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
</head>
<!-- END HEAD -->
<body>

    <div class="container">

        <h1 class="text-center h3">
            Акт выполненных работ № {{ $ticketManagement->getTicketNumber() }}
        </h1>

        <div class="row margin-bottom-30">
            <div class="col-xs-6">
                {{ $ticketManagement->management->name }}
            </div>
            <div class="col-xs-6 text-right">
                {{ $ticketManagement->ticket->created_at->formatLocalized( '%d %B %Y' ) }} г.
            </div>
        </div>

        <div class="row">
            <div class="col-xs-7">
                <dl>
                    <dt>
                        ФИО заявителя
                    </dt>
                    <dd>
                        {{ $ticketManagement->ticket->getName() ?? '-' }}
                    </dd>
                </dl>
            </div>
            <div class="col-xs-5">
                <dl>
                    <dt>
                        Телефон(ы) заявителя
                    </dt>
                    <dd>
                        {{ $ticketManagement->ticket->getPhones() ?? '-' }}
                    </dd>
                </dl>
            </div>
        </div>

        <dl>
            <dt>
                Адрес проблемы
            </dt>
            <dd>
                {{ $ticketManagement->ticket->getAddress() ?? '-' }}
            </dd>
        </dl>

        <dl>
            <dt>
                Тип проблемы
            </dt>
            <dd>
                {{ $ticketManagement->ticket->type->name ?? '-' }}
            </dd>
        </dl>

        <dl>
            <dt>
                Проблема
            </dt>
            <dd>
                {{ $ticketManagement->ticket->text ?? '-' }}
            </dd>
        </dl>

        <h2 class="h4 text-center">
            Выполненные работы
        </h2>

        <table class="table table-striped">
            <tr>
                <th width="65%">
                    Наименование
                </th>
                <th class="text-center">
                    Ед. Изм.
                </th>
                <th class="text-center">
                    Кол-во
                </th>
                <th class="text-right">
                    Стоимость
                </th>
            </tr>
        @foreach ( $services as $service )
            <tr>
                <td>
                    {{ $service->name }}
                </td>
                <td class="text-center">
                    {{ $service->unit }}
                </td>
                <td class="text-center">
                    {{ number_format( $service->quantity, 2 ) }}
                </td>
                <td class="text-right">
                    {{ number_format( $service->amount, 2 ) }}
                </td>
            </tr>
        @endforeach
        @for ( $i = 0; $i < $lines; $i ++ )
            <tr>
                <td>
                    &nbsp;
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
        @endfor
            <tr>
                <th colspan="3" class="text-right">
                    Итого
                </th>
                <th class="text-right">
                    @if ( $total )
                        {{ number_format( $total, 2 ) }}
                    @endif
                </th>
            </tr>
        </table>

        <div class="row margin-top-50">
            <div class="col-xs-3 text-right">
                ФИО Исполнителя
            </div>
            <div class="col-xs-5 border-bottom">
                &nbsp;
            </div>
            <div class="col-xs-2 text-right">
                Подпись
            </div>
            <div class="col-xs-2 border-bottom">
                &nbsp;
            </div>
        </div>

        <div class="row margin-top-30">
            <div class="col-xs-3 text-right">
                ФИО Заявителя
            </div>
            <div class="col-xs-5 border-bottom">
                &nbsp;
            </div>
            <div class="col-xs-2 text-right">
                Подпись
            </div>
            <div class="col-xs-2 border-bottom">
                &nbsp;
            </div>
        </div>

        <div class="row margin-top-30">
            <div class="col-xs-8 text-right">
                Дата
            </div>
            <div class="col-xs-4 border-bottom text-right">
                г.
            </div>
        </div>

    </div>

    <script>
        window.print();
    </script>

</body>
</html>