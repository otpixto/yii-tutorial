{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'phone', $ticket->phone, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'phone2', $ticket->phone2, [ 'class' => 'form-control' ] ) !!}
	</div>
</div>
{!! Form::close() !!}