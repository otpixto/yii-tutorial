@include( 'parts.head' )

<body>

<!-- BEGIN CONTAINER -->
<div class="wrapper">

    <!-- BEGIN HEADER -->
    <header class="page-header">
        <nav class="navbar mega-menu" role="navigation">
            <div class="container-fluid">
                <div class="page-nav">

                    <!-- Brand and toggle get grouped for better mobile display -->
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                        <span class="sr-only">Навигация</span>
                        <span class="toggle-icon">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </span>
                    </button>
                    <!-- End Toggle Button -->

                    <!-- BEGIN LOGO -->
                    <a id="index" class="page-logo" href="/">

                        <img src="{{ \App\Models\Provider::getLogo() }}" alt="{{ \Config::get( 'app.name' ) }}" />
                        <span class="page-title">
                            @if ( \App\Models\Provider::isOperatorUrl() )
                                <span class="font-red-intense">
                                    Оператор
                                </span>
                            @elseif ( \App\Models\Provider::getCurrent() )
                                <span class="font-white">
                                    {{ \App\Models\Provider::$current->name }}
                                </span>
                            @endif
                        </span>
                    </a>
                    <!-- END LOGO -->
                    
                    {{--<!-- BEGIN TOPBAR ACTIONS -->
                    <div class="topbar-actions">

                        --}}{{--@include( 'parts.notification' )--}}{{--

                        --}}{{--@include( 'parts.count' )--}}{{--

                        @include( 'parts.phone' )
                        @include( 'parts.user_profile' )

                    </div>
                    <!-- END TOPBAR ACTIONS -->--}}
                </div>

                @include( 'parts.header_menu' )

            </div>
            <!--/container-->
        </nav>
    </header>
    <!-- END HEADER -->
    <div class="container-fluid">
        <div class="page-content">

            <!-- BEGIN BREADCRUMBS -->
            <div class="breadcrumbs" id="breadcrumbs">

                <h1 class="title">
                    {{ \App\Classes\Title::get() }}
                    @if ( isset( $ticket ) && $ticket->vendor && $ticket->vendor_number )
                        <span class="small">
                            {{ $ticket->vendor->name }}
                            №
                            <b>
                                {{ $ticket->vendor_number }}
                            </b>
                            @if ( $ticket->vendor_date )
                                от
                                <b>
                                    {{ $ticket->vendor_date->format( 'd.m.Y' ) }}
                                </b>
                            @endif
                        </span>
                    @endif
                </h1>

                @yield( 'breadcrumbs' )

            </div>
            <!-- END BREADCRUMBS -->

            <!-- BEGIN SIDEBAR CONTENT LAYOUT -->
            <div class="page-content-container">
                <div class="page-content-row">
                    <div class="page-content-col">

                        @include( 'parts.errors' )
                        @include( 'parts.success' )

                        <!-- BEGIN PAGE BASE CONTENT -->
                        @yield( 'content' )
                        <!-- END PAGE BASE CONTENT -->

                    </div>
                </div>
            </div>
            <!-- END SIDEBAR CONTENT LAYOUT -->
        </div>

        @include( 'parts.footer' )

    </div>
</div>
<!-- END CONTAINER -->

@include( 'parts.js' )

</body>
</html>