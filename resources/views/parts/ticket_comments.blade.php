@if ( \Auth::user()->can( 'tickets.comments' ) && $comments->count() )
    <tr @if ( in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ) class="text-muted opacity" @endif id="ticket-comments-{{ $ticketManagement->id }}">
        <td colspan="{{ ( 5 + ( \Auth::user()->can( 'tickets.field_operator' ) ? 1 : 0 ) + ( \Auth::user()->can( 'tickets.field_management' ) ? 1 : 0 ) ) }}">
            @if ( $ticketManagement->rate_comment )
                <div class="note note-danger">
                    <span class="small text-muted">Комментарий к оценке:</span>
                    {{ $ticketManagement->rate_comment }}
                </div>
            @endif
            <div class="note note-info">
                @include( 'parts.comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $comments ] )
            </div>
        </td>
    </tr>
@endif