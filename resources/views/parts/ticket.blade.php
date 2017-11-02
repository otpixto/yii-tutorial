<tr @if ( in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ) class="text-muted opacity" @endif>
    <td>
        <div class="mt-element-ribbon" id="ticket-status-{{ $ticketManagement->getTicketNumber() }}">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticketManagement->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="color-inherit">
                    {{ $ticketManagement->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
            #{{ $ticketManagement->getTicketNumber() }}
        @if ( $ticketManagement->rate )
            <span class="pull-right">
                @include( 'parts.rate', [ 'ticketManagement' => $ticketManagement ] )
            </span>
        @endif
    </td>
    <td>
        {{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}
    </td>
    @if ( $field_operator )
        <td>
            <span class="{{ $ticketManagement->ticket->author->id == \Auth::user()->id ? 'mark' : '' }}">
                {{ $ticketManagement->ticket->author->getShortName() }}
            </span>
        </td>
    @endif
    @if ( $field_management )
        <td>
            {{ $ticketManagement->management->name }}
        </td>
    @endif
    <td>
        @if ( $ticketManagement->ticket->type )
            <div class="bold">
                {{ $ticketManagement->ticket->type->category->name }}
            </div>
            <div class="small">
                {{ $ticketManagement->ticket->type->name }}
            </div>
        @endif
    </td>
    <td>
        {{ $ticketManagement->ticket->getAddress() }}
        @if ( $ticketManagement->ticket->getPlace() )
            <span class="small text-muted">
                ({{ $ticketManagement->ticket->getPlace() }})
            </span>
        @endif
    </td>
    <td class="text-right hidden-print">
        <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="btn btn-lg btn-primary tooltips" title="Открыть заявку #{{ $ticketManagement->getTicketNumber() }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( \Auth::user()->can( 'tickets.comments' ) && $ticketManagement->comments->merge( $ticketManagement->ticket->comments )->count() )
    <tr>
        <td colspan="{{ ( 5 + ( $field_operator ? 1 : 0 ) + ( $field_management ? 1 : 0 ) ) }}">
            @if ( $ticketManagement->rate_comment )
                <div class="note note-danger">
                    <span class="small text-muted">Комментарий к оценке:</span>
                    {{ $ticketManagement->rate_comment }}
                </div>
            @endif
            <div class="note note-info">
                @include( 'parts.comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $ticketManagement->comments->merge( $ticketManagement->ticket->comments )->sortBy( 'id' ) ] )
            </div>
        </td>
    </tr>
@endif