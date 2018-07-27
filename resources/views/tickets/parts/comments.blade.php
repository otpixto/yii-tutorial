<tr class="comments @if ( $ticket->isFinalStatus() ) text-muted opacity @endif @if( ! $comments->count() ) hidden @endif" data-ticket-comments="{{ $ticket->id }}">
	<td colspan="6">
		@if ( $ticket->status_code == 'waiting' && ! empty( $ticket->postponed_comment ) )
			<div class="note note-warning">
				<span class="small text-muted">Комментарий к отложенной заявке:</span>
				{{ $ticket->postponed_comment }}
			</div>
		@endif
		@if ( isset( $ticketManagement ) && $ticketManagement->rate_comment )
			<div class="note note-danger">
				<span class="small text-muted">Комментарий к оценке:</span>
				{{ $ticketManagement->rate_comment }}
			</div>
		@endif
		@if ( \Auth::user()->can( 'tickets.comments' ) && $comments->count() )
			<div class="text-center hidden-print">
				<a class="text-primary small bold" data-toggle="#tickets-comments-{{ $ticketManagement->id }}">
					Показать \ скрыть комментарии ({{ $comments->count() }})
				</a>
			</div>
			<div class="note note-info hidden" id="tickets-comments-{{ $ticketManagement->id }}">
				@include( 'parts.comments', [ 'origin' => $ticket, 'comments' => $comments ] )
			</div>
		@endif
	</td>
</tr>