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
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="{{ \App\Classes\Title::render() }}" name="description" />
    <meta content="{{ \Config::get( 'app.author' ) }}" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/assets/pages/css/error.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="/images/favicon.ico" />
    <!-- END HEAD -->
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1 class="big text-danger bold">
                        {{ $exception->getStatusCode() }}
                    </h1>
                    <h2>
                        @if ( ! empty( $exception->getMessage() ) )
                            @php( $error = $exception->getMessage() )
                        @else
                            @switch ( $exception->getStatusCode() )
                                @case ( 403 )
                                    @php( $error = 'Доступ запрещен' )
                                @break
                                @case ( 404 )
                                    @php( $error = 'Страница не найдена' )
                                @break
                                @case ( 423 )
                                @case ( 429 )
                                    @php( $error = 'Заблокировано' )
                                @break
                                @case ( 500 )
                                    @php( $error = 'Внутренняя ошибка сервера' )
                                @break
                                @default
                                    @php( $error = 'Неизвестная ошибка' )
                                @break
                            @endswitch
                        @endif
                        {{ $error }}
                    </h2>
                    <div class="error-actions">
                        <a href="{{ route( 'tickets.index' ) }}" class="btn btn-primary btn-lg">
                            <i class="glyphicon glyphicon-home"></i>
                            <span class="hidden-xs">
                                На главную
                            </span>
                        </a>
                        <a href="mailto:{{ urlencode( config( 'mail.support' ) ) }}?subject=Error%20{{ $exception->getStatusCode() }}&body=url:%20{{ url()->full() }}%0D%0Amessage:%20{{ str_replace( ' ', '%20', $error ) }}" class="btn btn-default btn-lg">
                            <i class="glyphicon glyphicon-envelope"></i>
                            <span class="hidden-xs">
                                Написать письмо
                            </span>
                        </a>
                    </div>
                    @if ( \Auth::user() )
                        <div class="text-center">
                            <a href="{{ route( 'logout' ) }}">Выход</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>