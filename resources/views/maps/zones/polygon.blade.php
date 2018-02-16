@if ( isset( $object ) )
    {!! Form::model( $object, [ 'method' => 'put', 'route' => [ 'zones.update', $object->id ], 'class' => 'form-horizontal submit-loading ajax', 'id' => 'form-geometry' ] ) !!}
    {!! Form::hidden( 'id', $object->id ) !!}
@else
    {!! Form::open( [ 'url' => route( 'zones.store' ), 'class' => 'form-horizontal submit-loading ajax', 'id' => 'form-geometry' ] ) !!}
    {!! Form::hidden( 'type', 'Polygon' ) !!}
@endif
{!! Form::hidden( 'coordinates', null ) !!}
<div class="form-group">
    {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::select( 'management_id', [ null => 'Выберите из списка' ] + $managements->toArray(), $object->management_id ?? null, [ 'class' => 'form-control select2', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::text( 'name', $object->name ?? null, [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'fillColor', 'Цвет заливки', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::color( 'fillColor', $object->fillColor ?? '#337ab7', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'strokeColor', 'Цвет границ', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::color( 'strokeColor', $object->strokeColor ?? '#333333', [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
{!! Form::close() !!}