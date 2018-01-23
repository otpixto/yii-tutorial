{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'lastname', $ticket->lastname, [ 'class' => 'form-control text-capitalize', 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'firstname', $ticket->firstname, [ 'class' => 'form-control text-capitalize', 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'middlename', $ticket->middlename, [ 'class' => 'form-control text-capitalize' ] ) !!}
	</div>
</div>
{!! Form::close() !!}