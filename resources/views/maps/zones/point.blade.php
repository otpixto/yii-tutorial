@if ( isset( $object ) )
    {!! Form::model( $object, [ 'method' => 'put', 'route' => [ 'zones.update', $object->id ], 'class' => 'form-horizontal submit-loading ajax', 'id' => 'form-geometry' ] ) !!}
    {!! Form::hidden( 'id', $object->id ) !!}
@else
    {!! Form::open( [ 'url' => route( 'zones.store' ), 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
    {!! Form::hidden( 'type', 'Polygon' ) !!}
@endif
{!! Form::hidden( 'coordinates', null ) !!}
<div class="form-group">
    {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::select( 'management_id', $managements, $object->management_id ?? null, [ 'class' => 'form-control select2', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        {!! Form::text( 'name', $object->name ?? null, [ 'class' => 'form-control', 'required' ] ) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label( null, 'Иконка', [ 'class' => 'control-label col-xs-4' ] ) !!}
    <div class="col-xs-8">
        <div class="row">
        @foreach ( \App\Models\Geometry::$presets as $preset )
            <div class="col-xs-3">
                <label>
                    <input type="radio" name="preset" value="{{ $preset }}" @if ( ( isset( $object ) && $object->preset == $preset ) || ( ! isset( $object ) && $preset == 'islands#nightDotIcon' ) ) checked="checked" @endif />
                    <img src="/images/presets/{{ urlencode( $preset ) }}.png" alt="{{ $preset }}" />
                </label>
            </div>
        @endforeach
        </div>
    </div>
</div>
{!! Form::close() !!}