@foreach ( $comments as $comment )
	<div class="media" id="comment-{{ $comment->id }}">
		<i class="fa fa-caret-right pull-left"></i>
		<div class="media-body">
			<h5 class="media-heading">
				<a href="javascript:;">
					{!! $comment->author->getPosition() !!}
					{{ $comment->author->getShortName() }}
				</a>
				<span class="small">
					{{ $comment->created_at->format( 'd.m.Y H:i' ) }}
				</span>
				<span class="media-buttons">
					@if ( isset( $ticketManagement ) && $ticketManagement->canComment() )
						<button type="button" class="btn btn-xs btn-info hidden-print" data-action="comment" data-model-name="{{ get_class( $comment ) }}" data-model-id="{{ $comment->id }}" data-origin-model-name="{{ get_class( $ticketManagement ) }}" data-origin-model-id="{{ $ticketManagement->id }}" data-file="1">
						<i class="fa fa-commenting pull-left"></i>
						<span class="visible-lg pull-right">
							ответить
						</span>
					</button>
					@endif
					@if ( \Auth::user()->can( 'tickets.comments_delete' ) )
						<button type="button" class="btn btn-xs btn-danger hidden-print" data-action="comment_delete" data-id="{{ $comment->id }}">
							<i class="fa fa-close pull-left"></i>
							<span class="visible-lg pull-right">
								удалить
							</span>
						</button>
					@endif
				</span>
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
				@include( 'parts.comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $comment->childs ] )
			@endif
		</div>
	</div>
@endforeach