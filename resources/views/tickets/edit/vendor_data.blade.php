{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}

<div class="form-group">
    {!! Form::label( 'vendor_number', 'Номер вендора', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-4">
        {!! Form::text( 'vendor_number', $ticket->vendor_number, [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label( 'vendor_date', 'от', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-4">
        {!! Form::date( 'vendor_date', $ticket->vendor_date ? $ticket->vendor_date->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>

</div>

{!! Form::close() !!}
