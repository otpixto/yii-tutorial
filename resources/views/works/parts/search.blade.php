{!! Form::open( [ 'method' => 'post', 'url' => route( 'works.filter' ), 'id' => 'search-form' ] ) !!}
{!! Form::hidden( 'search', 1 ) !!}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Номер сообщения
    </h4>
    <div class="col-md-4">
        {!! Form::text( 'id', \Input::get( 'id' ), [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес работ
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'building_id', $building, \Input::get( 'building_id' ), [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проблемы' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Сегмент
    </h4>
    <div class="col-md-10">
        <span id="segment" class="form-control text-muted">
            @if ( $segment )
                {{ $segment->name }}
            @else
                Нажмите, чтобы выбрать
            @endif
        </span>
        {!! Form::hidden( 'segment_id', \Input::get( 'segment_id' ), [ 'id' => 'segment_id' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        УО
    </h4>
    <div class="col-md-10">
        <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements" name="managements[]">
            @foreach ( $availableManagements as $management => $arr )
                <optgroup label="{{ $management }}">
                    @foreach ( $arr as $management_id => $management_name )
                        <option value="{{ $management_id }}" @if ( in_array( $management_id, $managements ) ) selected="selected" @endif>
                            {{ $management_name }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
</div>
<hr />
@if ( count( $providers ) > 1 )
    <div class="row margin-top-10">
        <h4 class="col-md-2">
            Поставщик
        </h4>
        <div class="col-md-10">
            {!! Form::select( 'provider_id', [ null => 'ВСЕ (' . count( $providers ) . ')' ] + $providers->toArray(), \Input::get( 'provider_id' ), [ 'class' => 'form-control select2' ] ) !!}
        </div>
    </div>
@endif
<div class="row">
    <h4 class="col-md-2">
        Начало действия
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            {!! Form::text( 'begin_from', \Input::get( 'begin_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">-</span>
            {!! Form::text( 'begin_to', \Input::get( 'begin_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
<div class="row">
    <h4 class="col-md-2">
        Конец действия
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            {!! Form::text( 'end_from', \Input::get( 'end_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">-</span>
            {!! Form::text( 'end_to', \Input::get( 'end_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Классификатор
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'category_id', [ null => ' -- все -- ' ] + $categories->toArray(), \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Основание
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'reason', \Input::get( 'reason' ), [ 'class' => 'form-control' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Состав работ
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'composition', \Input::get( 'composition' ), [ 'class' => 'form-control' ] ) !!}
    </div>
</div>
<hr />
<div class="row">
    <div class="col-xs-12">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
        <a href="{{ route( 'works.index' ) }}" class="btn btn-danger btn-lg">
            Сбросить фильтр
        </a>
    </div>
</div>
{!! Form::close() !!}