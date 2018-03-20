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

        <h3 class="text-right">
            <i>{{ $ticketManagement->management->name }}</i>
        </h3>
		
		<p><br /></p>

        <h1 class="text-center">
            Акт выполненных работ
        </h1>
		
		<p><br /></p>
		<p><br /></p>

        <p>
            По заявке <b>№ {{ $ticketManagement->ticket_id }}</b> от <b>{{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}</b>
        </p>

        @if ( $ticketManagement->statusesHistory()->first() && $ticketManagement->statusesHistory()->first()->author )
            <p>
                Заявку принял <b>{{ $ticketManagement->statusesHistory()->first()->author->getName() ?? '-' }}</b>
            </p>
        @endif

        <p>
            ФИО заявителя <b>{{ $ticketManagement->ticket->getName() ?? '-' }}</b>
        </p>

        <p>
            Телефон(ы) заявителя <b>{{ $ticketManagement->ticket->getPhones() ?? '-' }}</b>
        </p>

        <p>
            Адрес проблемы <b>{{ $ticketManagement->ticket->getAddress() ?? '-' }}</b>
        </p>

        <p>
            Тип проблемы: <b>{{ $ticketManagement->ticket->type->name ?? '-' }}</b>
        </p>

        <hr />

        <p>
            Проблема: <b>{{ $ticketManagement->ticket->text ?? '-' }}</b>
        </p>

        <hr />

        <h3>
            Выполненные работы
        </h3>

        <table class="table table-striped">
            <tr>
                <th with="85%">
                    Наименование
                </th>
                <th>
                    Кол-во
                </th>
            </tr>
        @foreach ( $works as $work )
            <tr>
                <td>
                    {{ $work->name }}
                </td>
                <td>
                    {{ $work->quantity }}
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
            </tr>
        @endfor
        </table>

        <p class="text-right bold">
            Устранено:
        </p>

        <p class="text-right">
            ФИО Исполнителя ______________________________________________ Подпись ___________
        </p>

        <p class="text-right bold">
            Принял:
        </p>

        <p class="text-right">
            ФИО Заявителя ________________________________________________ Подпись ___________
        </p>

        <p class="text-right">
            Дата ____________________________
        </p>

    </div>

</body>
</html>