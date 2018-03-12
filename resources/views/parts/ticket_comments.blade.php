<tr @if ( $ticket->isFinalStatus() ) class="text-muted opacity" @endif data-ticket-comments="{{ $ticket->id }}">
	<td colspan="{{ ( 6 + ( \Auth::user()->can( 'tickets.field_operator' ) ? 1 : 0 ) ) }}">
		@if ( isset( $ticketManagement ) && $ticketManagement->rate_comment )
			<div class="note note-danger">
				<span class="small text-muted">Комментарий к оценке:</span>
				{{ $ticketManagement->rate_comment }}
			</div>
		@endif
		@if ( \Auth::user()->can( 'tickets.comments' ) && $comments->count() )
			<div class="note note-info">
				@include( 'parts.comments', [ 'ticket' => $ticket, 'ticketManagement' => $ticketManagement ?? null, 'comments' => $comments ] )
			</div>
		@endif
	</td>
</tr>