{!! Form::open( [ 'method' => 'post', 'url' => route( 'works.filter' ), 'id' => 'search-form' ] ) !!}
{!! Form::hidden( 'search', 1 ) !!}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Показать
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'show', ['' => 'Активные', 'period' => 'Все'], '', [ 'class' => 'form-control', 'data-placeholder' => 'Показать' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Номер сообщения
    </h4>
    <div class="col-md-4">
        {!! Form::text( 'id', '', [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес работ
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'building_id', [], '', [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проблемы' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Сегмент
    </h4>
    <div class="col-md-10">
        <div id="segment_id" data-name="segments[]"></div>
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
<div class="row">
    <h4 class="col-md-2">
        Начало действия
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            {!! Form::text( 'begin_from', '', [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">-</span>
            {!! Form::text( 'begin_to', '', [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
<div class="row">
    <h4 class="col-md-2">
        Конец действия
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            {!! Form::text( 'end_from', '', [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">-</span>
            {!! Form::text( 'end_to', '', [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Классификатор
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'category_id', [ null => ' -- все -- ' ] + $categories->toArray(), '', [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Тип отключения
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'type_id', [ null => ' -- все -- ' ] + \App\Models\Work::$types, '', [ 'class' => 'form-control', 'placeholder' => 'Тип отключения' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Основание
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'reason', '', [ 'class' => 'form-control' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Состав работ
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'composition', '', [ 'class' => 'form-control' ] ) !!}
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