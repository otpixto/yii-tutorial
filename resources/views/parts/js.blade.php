<!--[if lt IE 9]>
<script src="/assets/global/plugins/respond.min.js"></script>
<script src="/assets/global/plugins/excanvas.min.js"></script>
<script src="/assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/icheck/icheck.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-sweetalert/sweetalert.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.pulsate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-cookie-1.4.1/jquery.cookie.js"></script>
<script src="/assets/global/plugins/bootstrap-growl/jquery.bootstrap-growl.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/common.js?25" type="text/javascript"></script>
<script src="//system.eds-region.ru:8443/socket.io/socket.io.js" type="text/javascript"></script>
<script src="/assets/global/scripts/websocket.js?23" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/assets/layouts/layout5/scripts/layout.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $( document )

        .ready( function ()
        {
            getQueues( '{{ \App\Models\Provider::getCurrent() ? \App\Models\Provider::$current->queue : config( 'asterisk.queue' ) }}' );
        })

        .on( 'click', '#queues-info', function ( e )
        {
            e.preventDefault();
            getQueues( '{{ \App\Models\Provider::getCurrent() ? \App\Models\Provider::$current->queue : config( 'asterisk.queue' ) }}', true );
        })

        .on( 'mouseover', '#queues-info', function ()
        {
            getQueues( '{{ \App\Models\Provider::getCurrent() ? \App\Models\Provider::$current->queue : config( 'asterisk.queue' ) }}' );
        });
</script>
<!-- END THEME LAYOUT SCRIPTS -->
@yield( 'js' )