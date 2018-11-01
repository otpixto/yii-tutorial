{!! Form::open( [ 'url' => route( 'tickets.executor', $ticketManagement->id ), 'id' => 'executor-form', 'class' => 'submit-loading form-horizontal ajax' ] ) !!}
@if ( \Auth::user()->can( 'catalog.executors.create' ) )
    <div class="form-group" id="executor">
        {!! Form::label( 'executor_id', 'Исполнитель', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-7">
            {!! Form::select( 'executor_id', $executors, $ticketManagement->executor_id, [ 'class' => 'form-control select2', 'data-placeholder' => ' -- выберите из списка -- ' ] ) !!}
        </div>
        <div class="col-xs-2">
            <button type="button" class="btn btn-primary executor-toggle" data-toggle="#executor_create, #executor">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="hidden" id="executor_create">
        <div class="form-group">
            {!! Form::label( 'executor_name', 'Исполнитель', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-7">
                {!! Form::text( 'executor_name', '', [ 'class' => 'form-control', 'placeholder' => 'ФИО и должность ' ] ) !!}
            </div>
            <div class="col-xs-2">
                <button type="button" class="btn btn-danger executor-toggle" data-toggle="#executor_create, #executor">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'executor_phone', 'Контактный телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-9">
                {!! Form::text( 'executor_phone', '', [ 'class' => 'form-control mask_phone', 'placeholder' => 'Контактный телефон' ] ) !!}
            </div>
        </div>
    </div>
@else
    <div class="form-group" id="executor">
        {!! Form::label( 'executor_id', 'Исполнитель', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-9">
            {!! Form::select( 'executor_id', $executors, $ticketManagement->executor_id, [ 'class' => 'form-control select2', 'data-placeholder' => ' -- выберите из списка -- ' ] ) !!}
        </div>
    </div>
@endif
<hr />
<div class="form-group">
    {!! Form::label( 'scheduled_begin_date', 'Начало', [ 'class' => 'control-label col-xs-3' ] ) !!}
    <div class="col-xs-5">
        {!! Form::date( 'scheduled_begin_date', $ticketManagement->scheduled_begin ? $ticketManagement->scheduled_begin->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
    <div class="col-xs-4">
        {!! Form::time( 'scheduled_begin_time', $ticketManagement->scheduled_begin ? $ticketManagement->scheduled_begin->format( 'H:i' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'scheduled_end_date', 'Окончание', [ 'class' => 'control-label col-xs-3' ] ) !!}
    <div class="col-xs-5">
        {!! Form::date( 'scheduled_end_date', $ticketManagement->scheduled_end ? $ticketManagement->scheduled_end->format( 'Y-m-d' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
    <div class="col-xs-4">
        {!! Form::time( 'scheduled_end_time', $ticketManagement->scheduled_end ? $ticketManagement->scheduled_end->format( 'H:i' ) : '', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
{!! Form::close() !!}
<div class="margin-top-15 alert alert-warning hidden" id="executor-notice">
    Выбранное время занято
</div>
<script type="text/javascript">
    $( '.mask_phone' ).inputmask( 'mask', {
        'mask': '+7 (999) 999-99-99'
    });
</script>