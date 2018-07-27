{!! Form::model( $ticket, [ 'method' => 'put', 'route' => [ 'tickets.update', $ticket->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
{!! Form::hidden( 'id', $ticket->id ) !!}
<div class="form-group">
    {!! Form::label( 'scheduled_begin_date', 'Начало', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-4">
        {!! Form::date( 'scheduled_begin_date', $ticket->scheduled_begin ? $ticket->scheduled_begin->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
    <div class="col-xs-4">
        {!! Form::time( 'scheduled_begin_time', $ticket->scheduled_begin ? $ticket->scheduled_begin->format( 'H:i' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'scheduled_end_date', 'Окончание', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-4">
        {!! Form::date( 'scheduled_end_date', $ticket->scheduled_end ? $ticket->scheduled_end->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
    <div class="col-xs-4">
        {!! Form::time( 'scheduled_end_time', $ticket->scheduled_end ? $ticket->scheduled_end->format( 'H:i' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
{!! Form::close() !!}