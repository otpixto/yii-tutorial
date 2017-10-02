{!! Form::open( [ 'url' => route( 'files.store' ), 'files' => true, 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::file( 'files[]', [ 'class' => 'form-control', 'placeholder' => 'Выберите файл(ы)', 'multiple' ] ) !!}
	</div>
</div>
{!! Form::close() !!}