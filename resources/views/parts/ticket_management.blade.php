<tr>
    <td>
        <div class="mt-element-ribbon" id="ticket-status-{{ $ticket->id }}">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticket->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="color-inherit">
                    {{ $ticketManagement->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
        #{{ $ticket->id }}
        @if ( $ticket->rate )
            <span class="pull-right">
                @include( 'parts.rate', [ 'ticket' => $ticket ] )
            </span>
        @endif
    </td>
    <td>
        {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
    </td>
    <td>
        {{ $ticket->getAddress() }}
		<span class="small text-muted">
			({{ $ticket->getPlace() }})
		</span>
    </td>
    <td>
        <div class="bold">
            {{ $ticket->type->category->name }}
        </div>
        <div class="small">
            {{ $ticket->type->name }}
        </div>
    </td>
    <td class="text-right hidden-print">
        <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="btn btn-lg btn-primary tooltips" title="Открыть обращение #{{ $ticket->id }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>