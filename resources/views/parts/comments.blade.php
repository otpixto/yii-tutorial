@foreach ( $comments as $comment )
	<div class="media">
		<div class="media-left">
			<span class="media-object"></span> 
		</div>
		<div class="media-body">
			<h4 class="media-heading">
				<a href="#">{{ $comment->author->getName() }}</a>
				<span class="c-date">{{ $comment->created_at->format( 'd.m.Y H:i' ) }}</span>
				<button class="btn btn-xs btn-info" data-action="comment" data-model-name="{{ get_class( $comment ) }}" data-model-id="{{ $comment->id }}" data-file="1">
					<i class="fa fa-commenting"></i>
					ответить
				</button>
			</h4> 
			{{ $comment->text }}
			@if ( $comment->childs->count() )
				@include( 'parts.comments', [ 'comments' => $comment->childs ] )
			@endif
		</div>
	</div>
@endforeach