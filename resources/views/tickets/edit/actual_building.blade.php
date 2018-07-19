{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'actual_building_id', 'Адрес проживания', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::select( 'actual_building_id', $ticket->actualBuilding ? $ticket->actualBuilding()->pluck( 'name', 'id' ) : [], $ticket->actual_building_id, [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проживания', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проживания', 'required' ] ) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label( 'actual_flat', 'Кв. \ офис', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::text( 'actual_flat', $ticket->actual_flat, [ 'class' => 'form-control' ] ) !!}
	</div>
</div>
{!! Form::close() !!}