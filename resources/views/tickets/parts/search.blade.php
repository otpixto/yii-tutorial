{!! Form::open( [ 'method' => 'post', 'url' => route( 'tickets.filter' ), 'id' => 'search-form' ] ) !!}
{{--{!! Form::hidden( 'search', 1 ) !!}--}}
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Номер заявки
    </h4>
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-addon">#</span>
            {!! Form::text( 'ticket_id', '', [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">/</span>
            {!! Form::text( 'ticket_management_id', \Input::get( 'ticket_management_id' ), [ 'class' => 'form-control', 'placeholder' => '' ] ) !!}
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
        {!! Form::button( 'Сбросить фильтр', [ 'class' => 'btn btn-danger btn-lg', 'id' => 'filter-clear' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <h4 class="col-md-2">
        Поступило из
    </h4>
    <div class="col-md-4">
        {!! Form::select( 'vendor_id', [ null => ' -- выберите из списка -- ' ] + $vendors, \Input::get( 'vendor_id' ), [ 'class' => 'form-control select2', 'id' => 'vendor_id' ] ) !!}
    </div>
    <div class="col-md-6 vendor hidden">
        <div class="input-group">
            <span class="input-group-addon">№</span>
            {!! Form::text( 'vendor_number', \Input::old( 'vendor_number' ), [ 'class' => 'form-control', 'placeholder' => '№', 'id' => 'vendor_number', 'autocomplete' => 'off' ] ) !!}
            <span class="input-group-addon">от</span>
            {!! Form::date( 'vendor_date', \Input::old( 'vendor_date' ), [ 'class' => 'form-control', 'placeholder' => 'от', 'id' => 'vendor_date', 'autocomplete' => 'off' ] ) !!}
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
                {!! Form::select( 'building_id', [], \Input::get( 'building_id' ), [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проблемы' ] ) !!}
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
        <div id="segment_id" data-name="segments[]"></div>
    </div>
</div>
<div class="row">
    <h4 class="col-md-2">
        ФИО заявителя
    </h4>
    <div class="col-md-10">
        <div class="row">
            <div class="col-xs-4">
                {!! Form::text( 'lastname', '', [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Фамилия' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'firstname', '', [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Имя' ] ) !!}
            </div>
            <div class="col-xs-4">
                {!! Form::text( 'middlename', '', [ 'class' => 'form-control' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Отчество' ] ) !!}
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
                {!! Form::select( 'actual_building_id', [], \Input::get( 'actual_building_id' ), [ 'id' => 'actual_building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проживания' ] ) !!}
            </div>
            <div class="col-xs-2">
                <div class="input-group">
                    <span class="input-group-addon">кв.</span>
                    {!! Form::text( 'actual_flat', '', [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
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
        {!! Form::text( 'phone', '', [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
    </div>
</div>
<hr />

<div class="row margin-top-10">
    <h4 class="col-md-2">
        Истекает срок исполнения в течение:
    </h4>
    <div class="col-md-10">
        <select class="form-control" data-label="left" name="deadline_execution">
                <option value="" selected="selected">-</option>
                <option value="24">24 часа</option>
                <option value="48">48 часов</option>
                <option value="72">72 часа</option>
        </select>
    </div>
</div>
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
                <label>Назначено</label>
                <div class="input-group">
                    {!! Form::text( 'scheduled_from', \Input::get( 'scheduled_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    <span class="input-group-addon">-</span>
                    {!! Form::text( 'scheduled_to', \Input::get( 'scheduled_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                </div>
            </div>
            <div class="col-md-6">
                <label>Выполнено</label>
                <div class="input-group">
                    {!! Form::text( 'completed_from', \Input::get( 'completed_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                    <span class="input-group-addon">-</span>
                    {!! Form::text( 'completed_to', \Input::get( 'completed_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
                </div>
            </div>
        </div>
    </div>
</div>
<hr />
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
@if ( \Auth::user()->can( 'tickets.field_management' ) )
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
            Заявку оформил(а)
        </h4>
        <div class="col-md-10">
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="operators" name="operators[]">
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
<div class="row margin-top-10">
    <h4 class="col-md-2">
        История статусов
    </h4>
    <div class="col-md-10">
        {!! Form::select( 'history_status_code', [ null => '-' ] + $availableStatuses, \Input::get( 'history_status_code' ), [ 'class' => 'form-control select2' ] ) !!}
    </div>
</div>
<div class="row margin-top-10">
    <div class="col-md-10 col-md-offset-2">
        <div class="input-group">
            <span class="input-group-addon">
                Период
            </span>
            {!! Form::text( 'history_from', \Input::get( 'history_from' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
            <span class="input-group-addon">
                -
            </span>
            {!! Form::text( 'history_to', \Input::get( 'history_to' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => '' ] ) !!}
        </div>
    </div>
</div>
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
<hr />
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
                Аварийная
            </label>
{{--            <label>--}}
{{--                {!! Form::checkbox( 'dobrodel', 1, \Input::get( 'dobrodel' ), [ 'class' => 'icheck' ] ) !!}--}}
{{--                <i class="icon-heart"></i>--}}
{{--                Добродел--}}
{{--            </label>--}}
            <label>
                {!! Form::checkbox( 'from_lk', 1, \Input::get( 'from_lk' ), [ 'class' => 'icheck' ] ) !!}
                <i class="icon-user-follow"></i>
                Из ЛК
            </label>
            <label>
                {!! Form::checkbox( 'from_eais', 1, \Input::get( 'from_eais' ), [ 'class' => 'icheck' ] ) !!}
                <i class="icon-check"></i>
                Из ЕИАС
            </label>
            <label>
                {!! Form::checkbox( 'from_gzhi', 1, \Input::get( 'from_gzhi' ), [ 'class' => 'icheck' ] ) !!}
                <i class="icon-key"></i>
                Из ГЖИ
            </label>
        </div>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-xs-12">
        {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
        {!! Form::button( 'Сбросить фильтр', [ 'class' => 'btn btn-danger btn-lg', 'id' => 'filter-clear' ] ) !!}
    </div>
</div>
{!! Form::close() !!}
