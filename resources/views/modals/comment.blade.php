{!! Form::open( [ 'url' => route( 'comments.store' ), 'files' => isset( $with_file ) && $with_file, 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
{!! Form::hidden( 'origin_model_id', $origin_model_id ) !!}
{!! Form::hidden( 'origin_model_name', $origin_model_name ) !!}
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::textarea( 'text', null, [ 'class' => 'form-control submit-loading', 'required' ] ) !!}
	</div>
</div>
@if ( isset( $with_file ) && $with_file )
    <div class="form-group">
		<div class="col-xs-12">
			{!! Form::file( 'files[]', [ 'class' => 'form-control', 'placeholder' => 'Выберите файл(ы)', 'multiple' ] ) !!}
		</div>
    </div>
@endif
{!! Form::close() !!}