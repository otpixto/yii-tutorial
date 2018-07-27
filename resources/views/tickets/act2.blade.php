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
</head>
<!-- END HEAD -->
<body>
    {!! $content !!}
    <script>
        window.print();
    </script>
</body>
</html>