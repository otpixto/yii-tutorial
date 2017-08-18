@include( 'parts.head' )

<body class="page-header-fixed page-sidebar-closed-hide-logo">

<!-- BEGIN CONTAINER -->
<div class="wrapper">

    <!-- BEGIN HEADER -->
    <header class="page-header">
        <nav class="navbar mega-menu" role="navigation">
            <div class="container-fluid">
                <div class="clearfix navbar-fixed-top">

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
                        <img src="/images/logo2.png" alt="{{ \Config::get( 'app.name' ) }}" />
                    </a>
                    <!-- END LOGO -->

                    @include( 'parts.search' )

                    <!-- BEGIN TOPBAR ACTIONS -->
                    <div class="topbar-actions">

                        {{--@include( 'parts.notification' )--}}

                        @include( 'parts.count' )

                        @include( 'parts.phone' )
                        @include( 'parts.user_profile' )

                    </div>
                    <!-- END TOPBAR ACTIONS -->
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
            <div class="breadcrumbs">

                <h1>{{ \App\Classes\Title::get() }}</h1>

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