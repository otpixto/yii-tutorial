@include( 'auth.parts.head' )
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="">
        <img src="/images/logo.png" alt="{{ \Config::get( 'app.name' ) }}" />
        <p>
            @if ( \App\Models\Region::isOperatorUrl() )
                <span class="text-danger">Оператор</span>
            @elseif ( \App\Models\Region::getCurrent() )
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