@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

	{!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading hidden-print margin-bottom-15' ] ) !!}
	{!! Form::hidden( 'report', 1 ) !!}
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label' ] ) !!}
            <div class="input-group">
                {!! Form::text( 'date_from', $date_from->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
                <span class="input-group-addon">-</span>
                {!! Form::text( 'date_to', $date_to->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label' ] ) !!}
			{!! Form::select( 'management_id', $availableManagements, $management_id, [ 'class' => 'form-control select2' ] ) !!}
        </div>
    </div>
	
	<div class="row margin-top-15">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

@endsection