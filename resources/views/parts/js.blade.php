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
<script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.pulsate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-cookie-1.4.1/jquery.cookie.js"></script>
<script src="/assets/global/plugins/bootstrap-growl/jquery.bootstrap-growl.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/html2canvas.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/common.js?32" type="text/javascript"></script>
<script src="https://system.eds-region.ru:8443/socket.io/socket.io.js" type="text/javascript"></script>
<script src="/assets/global/scripts/websocket.js?35" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/assets/layouts/layout5/scripts/layout.min.js" type="text/javascript"></script>
@if ( \App\Models\Provider::getCurrent() )
    <script type="text/javascript">
        var ticketsAutoupdate = @php echo \Auth::user()->can( 'tickets.autoupdate' ) ? 'true;' : 'false;' @endphp
        $( document )

            .ready( function ()
            {
                getQueue();
            })

            .on( 'click', '#queues-info', function ( e )
            {
                e.preventDefault();
                getQueue( true );
            })

            .on( 'mouseover', '#queues-info', function ()
            {
                getQueue();
            });
    </script>
@endif
<!-- END THEME LAYOUT SCRIPTS -->
@yield( 'js' )
