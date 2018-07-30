@if ( \Auth::user()->can( 'tickets.counter' ) )
    <!-- BEGIN COUNT-->
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=not_processed" data-placement="bottom" class="ticket-ajax btn btn-sm btn-{{ \App\Classes\Counter::ticketsNotProcessedCount() == 0 ? 'default' : 'warning' }} tooltips" title="Необработанные заявки">
            <i class="fa fa-clock-o"></i>
            {{ \App\Classes\Counter::ticketsNotProcessedCount() }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=in_process" data-placement="bottom" class="ticket-ajax btn btn-sm btn-{{ \App\Classes\Counter::ticketsInProcessCount() == 0 ? 'default' : 'warning' }} tooltips" title="Заявки в работе">
            <i class="fa fa-wrench"></i>
            {{ \App\Classes\Counter::ticketsInProcessCount() }}
        </a>
    </div>
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'tickets.index' ) }}?show=completed" data-placement="bottom" class="ticket-ajax btn btn-sm btn-{{ \App\Classes\Counter::ticketsCompletedCount() == 0 ? 'default' : 'warning' }} tooltips" title="Выполненные заявки">
            <i class="fa fa-check-circle"></i>
            {{ \App\Classes\Counter::ticketsCompletedCount() }}
        </a>
    </div>
    <!-- END COUNT -->
@endif