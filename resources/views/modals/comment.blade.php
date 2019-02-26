{!! Form::open( [ 'url' => route( 'comments.store' ), 'files' => ( isset( $with_file ) && $with_file ), 'class' => 'form-horizontal submit-loading ajax', 'id' => 'comment-form' ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
{!! Form::hidden( 'reply_id', $reply_id ?? null ) !!}
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::textarea( 'text', null, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
@if ( isset( $with_file ) && $with_file && \Auth::user()->can( 'tickets.files' ) )
    <div class="form-group">
		<div class="col-xs-12">
			{!! Form::file( 'files[]', [ 'class' => 'form-control', 'placeholder' => 'Выберите файл(ы)', 'multiple' ] ) !!}
		</div>
    </div>
@endif
{!! Form::close() !!}