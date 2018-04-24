@if ( \Auth::user()->can( 'tickets.counter' ) )
    <!-- BEGIN COUNT-->
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=not_processed" data-placement="bottom" class="btn btn-sm btn-{{ \Session::get( 'count_not_processed' ) == 0 ? 'default' : 'warning' }} tooltips" title="Необработанные заявки">
            <i class="fa fa-clock-o"></i>
            {{ \Session::get( 'count_not_processed' ) }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=in_progress" data-placement="bottom" class="btn btn-sm btn-{{ \Session::get( 'count_in_progress' ) == 0 ? 'default' : 'warning' }} tooltips" title="Заявки в работе">
            <i class="fa fa-wrench"></i>
            {{ \Session::get( 'count_in_progress' ) }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=completed" data-placement="bottom" class="btn btn-sm btn-{{ \Session::get( 'count_completed' ) == 0 ? 'default' : 'warning' }} tooltips" title="Выполненные заявки">
            <i class="fa fa-check-circle"></i>
            {{ \Session::get( 'count_completed' ) }}
        </a>
    </div>
    <!-- END COUNT -->
@endif