@include( 'auth.parts.head' )
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="">
        <img src="/images/logo.png" alt="{{ \Config::get( 'app.name' ) }}" />
        <p>
            @if ( \Request::getHost() == \Session::get( 'settings' )->operator_domain )
                <span class="text-danger">Оператор</span>
            @else
                {{ \App\Models\Region::$current_region->name }}
            @endif
        </p>
    </a>
</div>
<!-- END LOGO -->
<div class="content">
    @yield( 'content' )
</div>
@include( 'auth.parts.footer' )