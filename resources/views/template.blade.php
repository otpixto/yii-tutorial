@include( 'parts.head' )
<body class="page-container-bg-solid page-header-menu-fixed">
<div class="page-wrapper">
    <div class="page-wrapper-row">
        <div class="page-wrapper-top">
            <!-- BEGIN HEADER -->
            <div class="page-header">
                <!-- BEGIN HEADER TOP -->
                <div class="page-header-top">
                    <div class="container">
                        <!-- BEGIN LOGO -->
                        <div class="page-logo" style="width: auto;">
                            <a href="/">
                                <h1>
                                    {{ Config::get( 'app.name' ) }}
                                </h1>
                            </a>
                        </div>
                        <!-- END LOGO -->
                        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
                        <a href="javascript:;" class="menu-toggler"></a>
                        <!-- END RESPONSIVE MENU TOGGLER -->
                        <!-- BEGIN TOP NAVIGATION MENU -->
                        <div class="top-menu">

                            <ul class="nav navbar-nav pull-right">

                                @include( 'parts.notification_head' )

                                <li class="droddown dropdown-separator">
                                    <span class="separator"></span>
                                </li>

                                @include( 'parts.inbox_head' )

                                @include( 'parts.profile_head' )

                                <!-- BEGIN QUICK SIDEBAR TOGGLER -->
                                <li class="dropdown dropdown-extended quick-sidebar-toggler">
                                    <span class="sr-only">Быстрая панель</span>
                                    <i class="icon-logout"></i>
                                </li>
                                <!-- END QUICK SIDEBAR TOGGLER -->

                            </ul>
                        </div>
                        <!-- END TOP NAVIGATION MENU -->
                    </div>
                </div>
                <!-- END HEADER TOP -->
                <!-- BEGIN HEADER MENU -->
                <div class="page-header-menu">
                    <div class="container">

                        @include( 'parts.search' )

                        @include( 'parts.nav_top' )

                    </div>
                </div>
                <!-- END HEADER MENU -->
            </div>
            <!-- END HEADER -->
        </div>
    </div>
    <div class="page-wrapper-row full-height">
        <div class="page-wrapper-middle">
            <!-- BEGIN CONTAINER -->
            <div class="page-container">
                <!-- BEGIN CONTENT -->
                <div class="page-content-wrapper">
                    <!-- BEGIN CONTENT BODY -->
                    <!-- BEGIN PAGE HEAD-->
                    <div class="page-head">
                        <div class="container">

                            <!-- BEGIN PAGE TITLE -->
                            <div class="page-title">
                                <h1>{{ $title ?? '&nbsp;' }}</h1>
                            </div>
                            <!-- END PAGE TITLE -->

                            @include( 'parts.toolbar' )

                        </div>
                    </div>
                    <!-- END PAGE HEAD-->
                    <!-- BEGIN PAGE CONTENT BODY -->
                    <div class="page-content">
                        <div class="container">

                            @yield( 'breadcrumbs' )

                            <!-- BEGIN PAGE CONTENT INNER -->
                            <div class="page-content-inner">

                                @include( 'parts.errors' )
                                @include( 'parts.success' )
                                @yield( 'content' )

                            </div>
                            <!-- END PAGE CONTENT INNER -->
                        </div>
                    </div>
                    <!-- END PAGE CONTENT BODY -->
                    <!-- END CONTENT BODY -->
                </div>
                <!-- END CONTENT -->

                @include( 'parts.sidebar' )

            </div>
            <!-- END CONTAINER -->
        </div>
    </div>
    <div class="page-wrapper-row">
        <div class="page-wrapper-bottom">
            @include( 'parts.footer' )
        </div>
    </div>
</div>

@include( 'parts.nav_right' )

@include( 'parts.js' )
</body>
</html>