@if ( \Auth::user()->can( 'tickets.counter' ) )
    <!-- BEGIN COUNT-->
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=not_processed" data-placement="bottom" class="btn btn-sm btn-{{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_not_processed_count' ) == 0 ? 'default' : 'warning' }} tooltips" title="Необработанные заявки">
            <i class="fa fa-clock-o"></i>
            {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_not_processed_count' ) }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=in_progress" data-placement="bottom" class="btn btn-sm btn-{{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_in_progress_count' ) == 0 ? 'default' : 'warning' }} tooltips" title="Заявки в работе">
            <i class="fa fa-wrench"></i>
            {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_in_progress_count' ) }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=completed" data-placement="bottom" class="btn btn-sm btn-{{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_completed_count' ) == 0 ? 'default' : 'warning' }} tooltips" title="Выполненные заявки">
            <i class="fa fa-check-circle"></i>
            {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_completed_count' ) }}
        </a>
    </div>
    <!-- END COUNT -->
@endif