<tr class="tickets @if ( in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ) text-muted opacity @elseif ( $ticketManagement->ticket->emergency ) danger @endif @if ( isset( $hide ) && $hide ) hidden @endif" id="ticket-{{ $ticketManagement->id }}">
    <td>
        <div class="mt-element-ribbon">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticketManagement->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="color-inherit">
                    {{ $ticketManagement->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
            <b>#{{ $ticketManagement->ticket->id }}</b><span class="text-muted small">/{{ $ticketManagement->id }}</span>
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
        @if ( $ticketManagement->ticket->emergency )
            <h3 class="margin-top-15 text-danger bold">
                <i class="icon-fire"></i>
                Авария
            </h3>
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
        <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="btn btn-lg btn-{{ in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ? 'info' : 'primary' }} tooltips" title="Открыть заявку #{{ $ticketManagement->getTicketNumber() }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( ! isset( $hideComments ) || ! $hideComments )
    @include( 'parts.ticket_comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $ticketManagement->comments->merge( $ticketManagement->ticket->comments )->sortBy( 'id' ) ] )
@endif