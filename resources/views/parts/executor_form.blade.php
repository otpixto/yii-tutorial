{!! Form::open( [ 'url' => route( 'tickets.executor' ), 'id' => 'executor-form', 'class' => 'submit-loading form-horizontal' ] ) !!}
{!! Form::hidden( 'id', $ticketManagement->id ) !!}
<div class="form-group">
    <div class="col-xs-12">
        {!! Form::select( 'executor_id', $executors, null, [ 'class' => 'select2 form-control' ] ) !!}
    </div>
</div>
<div class="form-group">
    <div class="col-xs-12">
        {!! Form::text( 'executor_name', null, [ 'class' => 'form-control', 'placeholder' => '... или создать нового' ] ) !!}
    </div>
</div>
<div class="form-group">
    <div class="col-xs-12 text-right">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success' ] ) !!}
    </div>
</div>
{!! Form::close() !!}