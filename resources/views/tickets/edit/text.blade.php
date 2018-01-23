{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	<div class="col-xs-12">
		{!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
	</div>
	<div class="col-xs-12">
		{!! Form::textarea( 'text', $ticket->text, [ 'class' => 'form-control', 'required' ] ) !!}
	</div>
</div>
{!! Form::close() !!}