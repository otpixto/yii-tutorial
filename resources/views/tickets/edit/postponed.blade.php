{!! Form::open( [ 'url' => route( 'tickets.postponed.update', $ticket->id ), 'id' => 'postpone-form', 'class' => 'submit-loading form-horizontal ajax' ] ) !!}
@if ( \Auth::user()->can( 'tickets.edit' ) )
    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::label( 'postponed_to', 'Дата', [ 'class' => 'control-label' ] ) !!} <span class="form-element-required">*</span>
            {!! Form::date( 'postponed_to', null, [ 'class' => 'form-control', 'required' => 'required' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::label( 'postpone_reason_id', 'Причина', [ 'class' => 'control-label' ] ) !!} <span class="form-element-required">*</span>
            {!! Form::select( 'postpone_reason_id', \App\Models\PostponeReason::pluck('name', 'id')->toArray(), "", [ 'class' => 'form-control', 'required' => 'required' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::label( 'postponed_comment', 'Комментарий', [ 'class' => 'control-label' ] ) !!}
            {!! Form::textarea( 'postponed_comment', null, [ 'class' => 'form-control' ] ) !!}
        </div>
    </div>
@endif
{!! Form::close() !!}
