{!! Form::open( [ 'method' => 'get', 'url' => route( 'customers.index' ), 'id' => 'search-form' ] ) !!}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес проживания
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-10">
                {!! Form::select( 'actual_building_id', $actual_building, \Input::get( 'actual_building_id' ), [ 'id' => 'actual_building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проживания' ] ) !!}
            </div>
            <div class="col-xs-2">
                <div class="input-group">
                    <span class="input-group-addon">кв.</span>
                    {!! Form::text( 'actual_flat', \Input::old( 'actual_flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
                </div>
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
<div class="row">
    <h4 class="col-md-2">
        ФИО заявителя
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-4">
                {!! Form::text( 'lastname', \Input::get( 'lastname' ), [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Фамилия' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'firstname', \Input::get( 'firstname' ), [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Имя' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'middlename', \Input::get( 'middlename' ), [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Отчество' ] ) !!}
            </div>
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Телефон заявителя
    </h4>
    <div class="col-md-4">
        {!! Form::text( 'phone', \Input::get( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
    </div>
</div>
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
		<a href="{{ route( 'customers.index' ) }}" class="btn btn-danger btn-lg">
			Сбросить фильтр
		</a>
    </div>
</div>
{!! Form::close() !!}