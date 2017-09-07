@if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
    <!-- BEGIN COUNT-->
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=not_processed" class="btn btn-sm btn-{{ \Session::get( 'count_not_processed' ) == 0 ? 'default' : 'warning' }} tooltips" title="Необработанные">
            <i class="fa fa-hourglass-2"></i>
            {{ \Session::get( 'count_not_processed' ) }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=not_completed" class="btn btn-sm btn-{{ \Session::get( 'count_not_completed' ) == 0 ? 'default' : 'danger' }} tooltips" title="Невыполненные">
            <i class="fa fa-remove"></i>
            {{ \Session::get( 'count_not_completed' ) }}
        </a>
    </div>
    <!-- END COUNT -->
@endif