@include( 'auth.parts.head' )
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="">
        <img src="/images/logo.png" alt="{{ \Config::get( 'app.name' ) }}" />
    </a>
</div>
<!-- END LOGO -->
<div class="content">
    @yield( 'content' )
</div>
@include( 'auth.parts.footer' )