@if ( \Auth::user()->admin || \Auth::user()->can( 'admin.calls.show' ) )
        <a href="{{ route( 'admin.missed_calls' ) }}" class="nav-link white font-white">
            Перезвонить
        </a>
        <span>&nbsp;</span>
        <span class="badge badge-danger left bold mr-5">{{ \App\Models\Asterisk\MissedCall::whereNull( 'call_id' )->count() }}</span>
    <span> &nbsp; &nbsp; &nbsp; </span>
@endif

@if ( \App\Models\Provider::getCurrent() && \App\Models\Provider::$current->queue && \Auth::user()->can( 'queues' ) )
    <!-- BEGIN QUEUES -->
    <span id="queues" class="margin-right-10">
        <a href="javascript:;" class="btn btn-xs btn-info hidden" id="queues-info">
            <i class="fa fa-info"></i>
        </a>
        <span id="queues-count" class="bold"></span>
    </span>
    <!-- END QUEUES -->
@endif
