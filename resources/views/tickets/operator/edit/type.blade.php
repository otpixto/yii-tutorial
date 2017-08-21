{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'type_id', 'Тип обращения', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::select( 'type_id', $types, $ticket->type_id, [ 'class' => 'form-control select2', 'required' ] ) !!}
	</div>
</div>
{!! Form::close() !!}