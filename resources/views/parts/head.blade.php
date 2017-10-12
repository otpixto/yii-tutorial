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
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if ( \Auth::user()->phoneSession )
        <meta name="user-phone" content="{{ \Auth::user()->phoneSession->number }}" />
    @endif
    {{--<meta content="width=device-width, initial-scale=1" name="viewport" />--}}
    <meta content="{{ \App\Classes\Title::render() }}" name="description" />
    <meta content="Dmitry Skabelin" name="author" />
    <link rel="shortcut icon" href="/images/favicon.ico" />
    @include( 'parts.css' )
</head>
<!-- END HEAD -->