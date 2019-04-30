{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
{!! Form::hidden( 'param', $param ) !!}
<div class="form-group">
	<div class="col-xs-4">
		<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
			{!! Form::checkbox( 'emergency', 1, $ticket->emergency ) !!}
			<span></span>
			Аварийная
		</label>
	</div>
	<div class="col-xs-4">
		<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
			{!! Form::checkbox( 'urgently', 1, $ticket->urgently ) !!}
			<span></span>
			Срочно
		</label>
	</div>
	<div class="col-xs-4">
		<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
			{!! Form::checkbox( 'dobrodel', 1, $ticket->dobrodel ) !!}
			<span></span>
			Добродел
		</label>
	</div>
</div>
{!! Form::close() !!}