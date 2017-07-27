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
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/assets/pages/css/act.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
</head>
<!-- END HEAD -->
<body>

    <div class="container">

        <h1 class="text-right">
            <i>{{ $ticketManagement->management->name }}</i>
        </h1>

        <h2 class="text-center">
            <u>
                Акт выполненных работ
            </u>
        </h2>

        <p>
            По заявке <b>№ {{ $ticketManagement->ticket_id }}</b> от <b>{{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}</b>
        </p>

        <p>
            Заявку принял <b>{{ $ticketManagement->statusesHistory()->first()->author->getName() }}</b>
        </p>

        <p>
            ФИО заявителя <b>{{ $ticketManagement->ticket->getName() }}</b>
        </p>

        <p>
            Адрес заявителя <b>{{ $ticketManagement->ticket->address }}</b>
        </p>

        <p>
            Тип проблемы: <b>{{ $ticketManagement->ticket->type->name }}</b>
        </p>

        <p>
            Проблема: <b>{{ $ticketManagement->ticket->text }}</b>
        </p>


        <p>
            Выполненные работы
        </p>

        <p>
            ________________________________________________________________________________________________________________

            <br /><br />
            ________________________________________________________________________________________________________________
            <br /><br />
            ________________________________________________________________________________________________________________
        </p>

        <p><br /></p>

        <p>
            Устранено:
        </p>

        <p>
            Дата _____________ ФИО Исполнителя ___________________________________________ Подпись _________________
        </p>

        <p><br /></p>

        <p>
            Принял:
        </p>

        <p>
            Дата _____________ ФИО Заявителя ______________________________________________ Подпись _________________
        </p>

    </div>

</body>
</html>