{!! Form::open( [ 'method' => 'get', 'url' => route( 'buildings.index' ), 'id' => 'search-form' ] ) !!}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::text( 'search', $request->get( '?search' ) ?? $request->get( 'search' ), [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>
    </div>
</div>

<div class="row margin-top-10">
    <h4 class="col-md-2">
        Сегмент
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::text( 'segment_name', $request->get( 'segment_name' ), [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>
    </div>
</div>

<div class="row margin-top-10">
    <h4 class="col-md-2">
        Родительский сегмент
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::text( 'parent_segment_name', $request->get( 'parent_segment_name' ), [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>
    </div>
</div>

<div class="row margin-top-10">
    <h4 class="col-md-2">
        Тип здания
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::select( 'building_type_id', $buildingTypes, $request->get( 'building_type_id' ), [ 'id' => 'parent_id', 'class' => 'form-control select2', 'placeholder' => 'Тип здания' ] ) !!}
            </div>
        </div>
    </div>
</div>

<hr/>
<div class="row">
    <div class="col-xs-12">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
        <a href="{{ route( 'buildings.index' ) }}" class="btn btn-danger btn-lg">
            Сбросить фильтр
        </a>
    </div>
</div>
{!! Form::close() !!}
