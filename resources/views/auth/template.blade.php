@include( 'auth.parts.head' )
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="">
        <img src="{{ \App\Models\Provider::getLogo() }}" alt="{{ \Config::get( 'app.name' ) }}" />
        <p>
            @if ( \App\Models\Provider::getCurrent() )
                <span class="font-white">
                    {{ \App\Models\Provider::$current->name }}
                </span>
            @endif
        </p>
    </a>
</div>
<!-- END LOGO -->
<div class="content">
    @yield( 'content' )
</div>
@include( 'auth.parts.footer' )