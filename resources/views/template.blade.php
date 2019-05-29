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
                    <button class="btn btn-info" data-action="push">
                        Отправить сообщение
                    </button>
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

<div class="hidden-md hidden-xs small hidden-print" id="user-info">
    @include( 'parts.queues' )
    @include( 'parts.phone' )
    <i class="fa fa-user"></i>
    <span class="bold">
        {{ \Auth::user()->getShortName() }}
    </span>
    <a href="{{ route( 'logout' ) }}">
        [выход]
    </a>
</div>

<div id="intercom" class="hidden hidden-print">
    <div id="intercom-title">
        ЗВОНОК НА 112
        <a href="javascript:;" class="pull-right text-danger" id="intercom-close">
            <i class="fa fa-times"></i>
        </a>
    </div>
    <a href="{{ route( 'tickets.create' ) }}" id="intercom-image"></a>
</div>

<div class="modal fade in hidden-print" role="basic" aria-hidden="true" data-id="modal" id="modal-push">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    Отправить сообщение
                </h4>
            </div>
            <div class="modal-body">
                <form action="{{ route( 'rest.intercom.push' ) }}" method="post" id="form-push" class="submit-loading ajax">
                    <div class="row">
                        <div class="col-xs-12">
                            <label for="push-title control-label">Тема</label>
                            <input type="text" name="title" id="push-title" value="" class="form-control" required="required" />
                        </div>
                    </div>
                    <div class="row margin-top-15">
                        <div class="col-xs-12">
                            <label for="push-message control-label">Сообщение</label>
                            <textarea name="message" class="form-control" rows="10" required="required"></textarea>
                        </div>
                    </div>
                    <div class="row margin-top-15">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-success">
                                Отправить
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn dark btn-outline" data-dismiss="modal">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

@include( 'parts.js' )
<script type="text/javascript">
    $( document )
        .on( 'click', '[data-action="push"]', function ( e )
        {
            e.preventDefault();
            $( '#modal-push' ).modal( 'show' );
        });
</script>

</body>
</html>