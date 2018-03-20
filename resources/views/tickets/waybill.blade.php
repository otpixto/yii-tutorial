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
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <style>
        .pagebreak {
            page-break-after: always;
        }
        h3, h4 {
            margin: 15px 0;
        }
    </style>
    <!-- END PAGE LEVEL STYLES -->
</head>
<!-- END HEAD -->
<body>

    <div class="container">

        @php( $i = 0 )

        @foreach ( $ticketManagements as $ticketManagement )

            <div>
                <h3 class="pull-left">
                    {{ $ticketManagement->management->name }}
                </h3>
                <h4 class="pull-right">
                    Наряд-заказ по заявке № {{ $ticketManagement->getTicketNumber() }}
                </h4>
            </div>

            <div class="clearfix"></div>

            <div >
                Адрес: <b>{{ $ticketManagement->ticket->getAddress() ?? '' }}</b>
            </div>

            <div >
                Дата подачи заявки: <b>{{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}</b>
            </div>

            <div >
                Исполнитель: <b>{{ $ticketManagement->executor->name ?? '' }}</b>
            </div>

            <div >
                Заявка <b>{{ $ticketManagement->ticket->text ?? '' }}</b>
            </div>

            <div class="margin-top-30">
                Дата и время выполнения работы ______________________________________________
            </div>

            <div class="margin-top-15">
                Подпись заявителя ______________________________________________________________
            </div>

            <hr />

            @if ( ++ $i == 4 )
                <div class="pagebreak"></div>
                @php( $i = 0 )
            @endif

        @endforeach

    </div>

    <script>
        window.print();
    </script>

</body>
</html>