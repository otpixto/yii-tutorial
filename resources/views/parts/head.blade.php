<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    {{--<meta name="viewport" content="width=device-width, initial-scale=1.0" />--}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if ( \Auth::user() && \Auth::user()->openPhoneSession )
        <meta name="user-phone" content="{{ \Auth::user()->openPhoneSession->number }}" />
    @endif
    {{--<meta content="width=device-width, initial-scale=1" name="viewport" />--}}
    <meta content="{{ \App\Classes\Title::render() }}" name="description" />
    <meta content="{{ \Config::get( 'app.author' ) }}" name="author" />
    <title>{{ \App\Classes\Title::render() }}</title>
    <link rel="shortcut icon" href="{{ \App\Models\Provider::getLogo() }}" />
    @include( 'parts.css' )
</head>
<!-- END HEAD -->