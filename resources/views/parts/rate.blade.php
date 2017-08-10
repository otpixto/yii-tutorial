@if ( $ticket->rate )
    <div class="pull-right text-nowrap tooltips text-{{ $ticket->rate < 4 ? 'danger' : 'success' }}" title="Оценка: {{ $ticket->rate }}">
        @for ( $i = 0; $i < $ticket->rate; $i ++ )
            <i class="fa fa-star"></i>
        @endfor
    </div>
@endif