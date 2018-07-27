{!! Form::open( [ 'method' => 'post', 'class' => 'submit-loading', 'url' => route( 'tickets.filter' ) ] ) !!}
{{--{!! Form::hidden( 'search', 1 ) !!}--}}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Номер заявки
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-addon">#</span>
            {!! Form::text( 'ticket_id', \Input::get( 'ticket_id' ), [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">/</span>
            {!! Form::text( 'ticket_management_id', \Input::get( 'ticket_management_id' ), [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Адрес проблемы
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-10">
                {!! Form::select( 'building_id', $building, \Input::get( 'building_id' ), [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проблемы' ] ) !!}
            </div>
            <div class="col-xs-2">
                <div class="input-group">
                    <span class="input-group-addon">кв.</span>
                    {!! Form::text( 'flat', \Input::get( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
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
        <span id="segment" class="form-control text-muted">
            @if ( $segment )
                {{ $segment->name }}
            @else
                Нажмите, чтобы выбрать
            @endif
        </span>
        {!! Form::hidden( 'segment_id', \Input::old( 'segment_id', $segment->id ?? null ), [ 'id' => 'segment_id' ] ) !!}
    </div>
</div>
<hr />
<div class="row">
    <h4 class="col-md-2">
        ФИО заявителя
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-4">
                {!! Form::text( 'lastname', \Input::get( 'lastname' ), [ 'class' => 'form-control customer-autocomplete', 'placeholder' => 'Фамилия' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'firstname', \Input::get( 'firstname' ), [ 'class' => 'form-control customer-autocomplete', 'placeholder' => 'Имя' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'middlename', \Input::get( 'middlename' ), [ 'class' => 'form-control customer-autocomplete', 'placeholder' => 'Отчество' ] ) !!}
            </div>
        </div>
    </div>
</div>
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
        Телефон заявителя
    </h4>
    <div class="col-md-4">
        {!! Form::text( 'phone', \Input::get( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
    </div>
</div>
<div class="hidden" id="additional-search">
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
            Периоды
        </h4>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-6">
                    <label>Дата создания</label>
                    <div class="input-group">
                        {!! Form::text( 'created_from', \Input::get( 'created_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                        <span class="input-group-addon">-</span>
                        {!! Form::text( 'created_to', \Input::get( 'created_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <label>Принято</label>
                    <div class="input-group">
                        {!! Form::text( 'accepted_from', \Input::get( 'accepted_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                        <span class="input-group-addon">-</span>
                        {!! Form::text( 'accepted_to', \Input::get( 'accepted_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>Выполнено</label>
                    <div class="input-group">
                        {!! Form::text( 'completed_from', \Input::get( 'completed_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                        <span class="input-group-addon">-</span>
                        {!! Form::text( 'completed_to', \Input::get( 'completed_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <label>Отложено</label>
                    <div class="input-group">
                        {!! Form::text( 'delayed_from', \Input::get( 'delayed_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                        <span class="input-group-addon">-</span>
                        {!! Form::text( 'delayed_to', \Input::get( 'delayed_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row margin-top-10">
        <h4 class="col-md-2">
            Статус(ы)
        </h4>
        <div class="col-md-10">
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="statuses" name="statuses[]">
                @foreach ( $availableStatuses as $status_code => $status_name )
                    <option value="{{ $status_code }}" @if ( in_array( $status_code, $statuses ) ) selected="selected" @endif>
                        {{ $status_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row margin-top-10">
        <h4 class="col-md-2">
            Классификатор
        </h4>
        <div class="col-md-10">
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="types" name="types[]">
                @foreach ( $availableTypes as $category => $arr )
                    <optgroup label="{{ $category }}">
                        @foreach ( $arr as $type_id => $type_name )
                            <option value="{{ $type_id }}" @if ( in_array( $type_id, $types ) ) selected="selected" @endif>
                                {{ $type_name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>
    @if ( \Auth::user()->can( 'tickets.field_management' ) && count( $availableManagements ) > 1 )
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
    @endif
    @if ( \Auth::user()->can( 'tickets.field_operator' ) && count( $availableOperators ) > 1 )
        <div class="row margin-top-10">
            <h4 class="col-md-2">
                Оператор(ы)
            </h4>
            <div class="col-md-10">
                <select class="form-control select2" multiple="multiple" id="operators" name="operators[]">
                    @foreach ( $availableOperators as $operator_id => $operator_name )
                        <option value="{{ $operator_id }}" @if ( in_array( $operator_id, $operators ) ) selected="selected" @endif>
                            {{ $operator_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    <hr />
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
    <div class="row">
        <div class="col-md-10 col-md-offset-2">
            <div class="icheck-inline">
                <label>
                    {!! Form::checkbox( 'overdue_acceptance', 1, \Input::get( 'overdue_acceptance' ), [ 'class' => 'icheck' ] ) !!}
                    Просрочено на принятие
                </label>
                <label>
                    {!! Form::checkbox( 'overdue_execution', 1, \Input::get( 'overdue_execution' ), [ 'class' => 'icheck' ] ) !!}
                    Просрочено на исполнение
                </label>
            </div>
        </div>
    </div>
    <div class="row margin-top-10">
        <div class="col-md-10 col-md-offset-2">
            <div class="icheck-inline">
                <label>
                    {!! Form::checkbox( 'emergency', 1, \Input::get( 'emergency' ), [ 'class' => 'icheck' ] ) !!}
                    <i class="icon-fire"></i>
                    Авария
                </label>
                <label>
                    {!! Form::checkbox( 'dobrodel', 1, \Input::get( 'dobrodel' ), [ 'class' => 'icheck' ] ) !!}
                    <i class="icon-heart"></i>
                    Добродел
                </label>
                <label>
                    {!! Form::checkbox( 'from_lk', 1, \Input::get( 'from_lk' ), [ 'class' => 'icheck' ] ) !!}
                    <i class="icon-user-follow"></i>
                    Из ЛК
                </label>
            </div>
        </div>
    </div>
</div>
<div class="row margin-top-15">
    <div class="col-md-2">
        <a href="javascript:;" data-toggle="#additional-search">
            <h4>
                <i class="fa fa-unsorted"></i>
                Доп. параметры
            </h4>
        </a>
    </div>
    <div class="col-md-10">
        {!! Form::submit( 'Применить', [ 'class' => 'btn blue-hoki btn-lg' ] ) !!}
        @if ( Input::get( 'search' ) )
            <a href="{{ route( 'tickets.index' ) }}" class="btn btn-default btn-lg">
                Сбросить фильтр
            </a>
        @endif
    </div>
</div>
{!! Form::close() !!}