@foreach ( $comments as $comment )
	<div class="media">
		<i class="fa fa-caret-right pull-left"></i>
		<div class="media-body">
			<h5 class="media-heading">
				<a href="javascript:;">
					@if ( $comment->author->hasRole( 'operator' ) )
						<b>[Оператор ЕДС]</b>
					@elseif ( $comment->author->hasRole( 'management' ) && $comment->author->management )
						<b>[{{ $comment->author->management->name }}]</b>
					@endif
					{{ $comment->author->getShortName() }}
				</a>
				<span class="small">
					{{ $comment->created_at->format( 'd.m.Y H:i' ) }}
				</span>
				@if ( isset( $ticket ) && $ticket->canComment() )
					<button class="btn btn-xs btn-info hidden-print" data-action="comment" data-model-name="{{ get_class( $comment ) }}" data-model-id="{{ $comment->id }}" data-origin-model-name="{{ get_class( $ticket ) }}" data-origin-model-id="{{ $ticket->id }}" data-file="1">
						<i class="fa fa-commenting"></i>
						ответить
					</button>
				@endif
			</h5>
			<p>
				{{ $comment->text }}
			</p>
			@if ( $comment->files->count() )
				<div class="note">
					<h5>Прикрепленные файлы:</h5>
					@foreach ( $comment->files as $file )
						<div>
							<a href="{{ route( 'files.download', [ 'id' => $file->id, 'token' => $file->getToken() ] ) }}">
								<i class="fa fa-file"></i>
								{{ $file->name }}
							</a>
						</div>
					@endforeach
				</div>
			@endif
			@if ( $comment->childs->count() )
				@include( 'parts.comments', [ 'ticket' => $ticket, 'comments' => $comment->childs ] )
			@endif
		</div>
	</div>
@endforeach