{!! Form::open( [ 'url' => route( 'tickets.status', $ticket->id ), 'class' => 'form-horizontal submit-loading', 'id' => 'postpone-form' ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
{!! Form::hidden( 'status_code', $status_code ) !!}
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::label( 'postponed_to', 'Дата', [ 'class' => 'control-label' ] ) !!}
		{!! Form::date( 'postponed_to', null, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::label( 'postponed_comment', 'Комментарий', [ 'class' => 'control-label' ] ) !!}
		{!! Form::textarea( 'postponed_comment', null, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
{!! Form::close() !!}