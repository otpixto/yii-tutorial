<ul class="media-list">
@foreach ( $comments as $comment )
	<li class="media" id="comment-{{ $comment->id }}">
		<a href="javascript:;" data-user="{{ $comment->author->id }}" class="pull-left">
			<img src="{{ $comment->author->getPhoto() }}" alt="" class="img-circle" height="40">
		</a>
		<div class="media-body">
			<a href="javascript:;" data-user="{{ $comment->author->id }}" class="small font-blue-soft">
				{{ $comment->author->getShortName( true ) }}
			</a>
			<small class="small font-grey-cascade">
				{{ $comment->created_at->format( 'd.m.Y H:i' ) }}
			</small>
			<span class="media-buttons">
				@if ( isset( $origin ) && $origin->canComment() )
					<button type="button" class="btn btn-xs btn-info hidden-print" data-action="comment" data-model-name="{{ get_class( $origin ) }}" data-model-id="{{ $origin->id }}" data-reply-id="{{ $comment->id }}" data-file="1">
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
			<div class="media-text">
				{{ $comment->text }}
			</div>
			@if ( $comment->files->count() )
				<ul class="list-inline media-files">
					@foreach ( $comment->files as $file )
						<li>
							<a class="small" href="{{ route( 'files.view', [ 'id' => $file->id, 'token' => $file->getToken() ] ) }}" target="_blank">
								<i class="fa fa-file"></i>
								{{ $file->name }}
							</a>
							<a href="{{ route( 'files.download', [ 'id' => $file->id, 'token' => $file->getToken() ] ) }}">
								<i class="fa fa-download"></i>
							</a>
						</li>
					@endforeach
				</ul>
			@endif
		</div>
	</li>
@endforeach
</ul>
