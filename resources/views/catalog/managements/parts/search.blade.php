{!! Form::open( [ 'method' => 'get', 'url' => route( 'managements.index' ), 'id' => 'search-form' ] ) !!}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::select( 'building_id', [], '', [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес' ] ) !!}
            </div>
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Сегмент
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'segment_id', 'Нажмите, чтобы выбрать', [ 'class' => 'form-control', 'id' => 'segment_id' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Родитель
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::select( 'parent_id', $parents, '', [ 'id' => 'parent_id', 'class' => 'form-control select2', 'placeholder' => 'Родитель' ] ) !!}
            </div>
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Категория
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::select( 'category_id', $categories, '', [ 'id' => 'category_id', 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <h4 class="col-md-2">
        Наименование
    </h4>
    <div class="col-md-10">
        {!! Form::text( 'name', '', [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Телефон
    </h4>
    <div class="col-md-4">
        {!! Form::text( 'phone', '', [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
    </div>
</div>
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
        Поиск по тегам
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-12">
                {!! Form::text( 'tags', \Input::get( 'tags' ), [ 'class' => 'form-control', 'placeholder' => 'Введите через запятую' ] ) !!}
            </div>
        </div>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-xs-12">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
		<a href="{{ route( 'managements.index' ) }}" class="btn btn-danger btn-lg">
			Сбросить фильтр
		</a>
    </div>
</div>
{!! Form::close() !!}