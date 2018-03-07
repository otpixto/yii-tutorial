@if ( $ticketManagement->rate )
    <span id="rate" class="text-nowrap tooltips text-{{ $ticketManagement->rate < 4 ? 'danger' : 'success' }}" title="Оценка: {{ $ticketManagement->rate }}">
        @for ( $i = 0; $i < $ticketManagement->rate; $i ++ )
            <i class="fa fa-star"></i>
        @endfor
    </span>
@endif