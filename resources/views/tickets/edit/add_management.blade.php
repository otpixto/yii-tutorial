{!! Form::open( [ 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
	{!! Form::label( 'management_id', 'ЭО', [ 'class' => 'control-label col-xs-4' ] ) !!}
	<div class="col-xs-8">
		{!! Form::select( 'management_id', [ null => ' -- выберите из списка -- ' ] + $managements->pluck( 'name', 'id' )->toArray(), null, [ 'class' => 'form-control select2', 'required' ] ) !!}
	</div>
</div>
{!! Form::close() !!}