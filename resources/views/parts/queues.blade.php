@if ( \Auth::user()->can( 'queues' ) )
    <!-- BEGIN QUEUES -->
    <span id="queues" class="margin-right-10">
        <a href="javascript:;" class="btn btn-xs btn-info hidden" id="queues-info">
            <i class="fa fa-info"></i>
        </a>
        <span id="queues-count" class="bold"></span>
    </span>
    <!-- END QUEUES -->
@endif