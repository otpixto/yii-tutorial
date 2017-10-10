{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'address_id', 'Адрес проблемы', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::select( 'address_id', $ticket->address->pluck( 'name', 'id' ), $ticket->address_id, [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проблемы', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес обращения', 'data-allow-clear' => true, 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'flat', 'Кв. \ офис', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'flat', $ticket->flat, [ 'class' => 'form-control' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'place', 'Проблемное место', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::select( 'place_id', \App\Models\Ticket::$places, $ticket->place_id, [ 'class' => 'form-control' ] ) !!}
	</div>
</div>
{!! Form::close() !!}