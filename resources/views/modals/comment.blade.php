{!! Form::open( [ 'url' => route( 'comments.store' ) ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
{!! Form::textarea( 'text', null, [ 'class' => 'form-control', 'required' ] ) !!}
{!! Form::close() !!}