{!! Form::open( [ 'url' => route( 'tickets.postponed.update', $ticket->id ), 'id' => 'postponed-form', 'class' => 'submit-loading form-horizontal ajax' ] ) !!}
@if ( \Auth::user()->can( 'tickets.edit' ) )
    <div class="form-group">
        {!! Form::label( 'postponed_to', 'Отложить до', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-9">
            {!! Form::date( 'postponed_to', $ticket->postponed_to ? $ticket->postponed_to->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label( 'postponed_comment', 'Комментарий', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-9">
            {!! Form::textarea( 'postponed_comment', $ticket->postponed_comment ?: '', [ 'class' => 'form-control' ] ) !!}
        </div>
    </div>
@endif
{!! Form::close() !!}