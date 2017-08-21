{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::textarea( 'text', $ticket->text, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
{!! Form::close() !!}