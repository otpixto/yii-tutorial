{!! Form::open( [ 'url' => route( 'tickets.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'ticket_id', $ticket->id ?? null, [ 'id' => 'ticket_id', 'autocomplete' => 'off' ] ) !!}

<div class="row">

    <div class="col-lg-7">

        @if ( $providers->count() > 1 )
            <div class="form-group">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label col-xs-3' ] ) !!}
                <div class="col-xs-9">
                    {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $ticket->provider_id ?? null ), [ 'class' => 'form-control select2 autosave', 'placeholder' => 'Поставщик', 'data-placeholder' => 'Поставщик', 'required', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>
        @endif

        <div class="form-group">
            {!! Form::label( 'type_id', 'Тип заявки', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-9">
                {!! Form::select( 'type_id', $types, \Input::old( 'type_id', $ticket->type_id ?? null ), [ 'class' => 'form-control select2 autosave', 'placeholder' => ' -- выберите из списка -- ', 'required', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

        <div id="types-description" class="alert alert-warning hidden"></div>

        <div class="form-group">
            {!! Form::label( 'building_id', 'Адрес проблемы', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-5">
                {!! Form::select( 'building_id', $ticket->building ? $ticket->building()->pluck( 'name', 'id' ) : [], \Input::old( 'building_id', $ticket->building_id ?? null ), [ 'class' => 'form-control autosave select2-ajax', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проблемы', 'required', 'autocomplete' => 'off' ] ) !!}
            </div>
            {!! Form::label( 'flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
            <div class="col-xs-3">
                {!! Form::text( 'flat', \Input::old( 'flat', $ticket->flat ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Кв. \ Офис', 'id' => 'flat', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'place_id', 'Проблемное место', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-9">
                {!! Form::select( 'place_id', [ null => ' -- выберите из списка -- ' ] + $places, \Input::old( 'place_id', $ticket->place_id ?? null ), [ 'class' => 'form-control autosave', 'required', 'id' => 'place_id', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'vendor_id', 'Поступило из', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-3">
                {!! Form::select( 'vendor_id', [ null => ' -- выберите из списка -- ' ] + $vendors, \Input::old( 'vendor_id', $ticket->vendor_id ?? null ), [ 'class' => 'form-control autosave', 'id' => 'vendor_id', 'autocomplete' => 'off' ] ) !!}
            </div>
            <div class="col-xs-6 vendor @if ( ! $ticket->vendor_id ) hidden @endif">
                <div class="input-group">
                    <span class="input-group-addon">№</span>
                    {!! Form::text( 'vendor_number', \Input::old( 'vendor_number', $ticket->vendor_number ), [ 'class' => 'form-control autosave', 'placeholder' => '№', 'id' => 'vendor_number', 'autocomplete' => 'off' ] ) !!}
                    <span class="input-group-addon">от</span>
                    {!! Form::date( 'vendor_date', \Input::old( 'vendor_date', $ticket->vendor_date ), [ 'class' => 'form-control autosave', 'placeholder' => 'от', 'id' => 'vendor_date', 'autocomplete' => 'off' ] ) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( null, '&nbsp;', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-3">
                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                    {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $ticket->emergency ?? null ), [ 'class' => 'autosave', 'id' => 'emergency', 'autocomplete' => 'off' ] ) !!}
                    <span></span>
                    Авария
                </label>
            </div>
            <div class="col-xs-3">
                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                    {!! Form::checkbox( 'urgently', 1, \Input::old( 'urgently', $ticket->urgently ?? null ), [ 'class' => 'autosave', 'autocomplete' => 'off' ] ) !!}
                    <span></span>
                    Срочно
                </label>
            </div>
        </div>

        <div class="form-group ">
            {!! Form::label( null, 'ФИО', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-3">
                {!! Form::text( 'lastname', \Input::old( 'lastname', $ticket->lastname ?? null ), [ 'id' => 'lastname', 'class' => 'form-control text-capitalize autosave' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Фамилия', 'required', 'autocomplete' => 'off' ] ) !!}
            </div>
            <div class="col-xs-3">
                {!! Form::text( 'firstname', \Input::old( 'firstname', $ticket->firstname ?? null ), [ 'id' => 'firstname', 'class' => 'form-control text-capitalize autosave' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Имя', 'required', 'autocomplete' => 'off' ] ) !!}
            </div>
            <div class="col-xs-3">
                {!! Form::text( 'middlename', \Input::old( 'middlename', $ticket->middlename ?? null ), [ 'id' => 'middlename', 'class' => 'form-control text-capitalize autosave' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Отчество', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-3">
                {!! Form::text( 'phone', \Input::old( 'phone', $ticket->phone ?? null ), [ 'id' => 'phone', 'class' => 'form-control mask_phone autosave' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Телефон', 'required', $ticket->customer_id ? 'readonly' : '', 'autocomplete' => 'off' ] ) !!}
            </div>
            {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-3">
                {!! Form::text( 'phone2', \Input::old( 'phone2', $ticket->phone2 ?? null ), [ 'id' => 'phone2', 'class' => 'form-control mask_phone autosave' . ( \Auth::user()->can( 'tickets.autocomplete' ) ? ' customer-autocomplete' : '' ), 'placeholder' => 'Доп. телефон', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'actual_building_id', 'Адрес проживания', [ 'class' => 'control-label col-xs-3' ] ) !!}
            <div class="col-xs-5">
                {!! Form::select( 'actual_building_id', $ticket->actual_building ? $ticket->actual_building()->pluck( 'name', 'id' ) : [], \Input::old( 'actual_building_id', $ticket->actual_building_id ?? null ), [ 'class' => 'form-control autosave select2-ajax', 'placeholder' => 'Адрес проживания', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес проживания', 'id' => 'actual_building_id', 'autocomplete' => 'off' ] ) !!}
            </div>
            {!! Form::label( 'actual_flat', 'Кв.', [ 'class' => 'control-label col-xs-1' ] ) !!}
            <div class="col-xs-3">
                {!! Form::text( 'actual_flat', \Input::old( 'actual_flat', $ticket->actual_flat ?? null ), [ 'class' => 'form-control autosave', 'placeholder' => 'Квартира', 'id' => 'actual_flat', 'autocomplete' => 'off' ] ) !!}
            </div>
        </div>

    </div>

    <div class="col-lg-5" id="info-block">

        <hr class="visible-sm" />

        <div class="form-group">
            {!! Form::label( null, 'Категория', [ 'class' => 'control-label col-md-5 col-xs-6 text-muted' ] ) !!}
            <div class="col-md-7 col-xs-6">
                <span class="form-control-static bold text-info" id="category">
                    ---
                </span>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label col-md-5 col-xs-6 text-muted' ] ) !!}
            <div class="col-md-7 col-xs-6">
                <span class="form-control-static bold text-info" id="season">
                    ---
                </span>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( null, 'Период на принятие заявки в работу', [ 'class' => 'control-label col-md-7 col-xs-6 text-muted' ] ) !!}
            <div class="col-md-5 col-xs-6">
                <span class="form-control-static bold text-info" id="period_acceptance">
                    ---
                </span>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( null, 'Период на исполнение', [ 'class' => 'control-label col-md-7 col-xs-6 text-muted' ] ) !!}
            <div class="col-md-5 col-xs-6">
                <span class="form-control-static bold text-info" id="period_execution">
                    ---
                </span>
            </div>
        </div>

        <div id="select"></div>

    </div>

</div>

<hr class="visible-sm" />

<div class="row">

    <div class="col-xs-12">

        <button type="button" class="btn btn-default margin-bottom-5" id="microphone" data-state="off">
            <i class="fa fa-microphone-slash"></i>
        </button>
        {!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
        {!! Form::textarea( 'text', \Input::old( 'text', $ticket->text ?? null ), [ 'class' => 'form-control autosizeme autosave', 'placeholder' => 'Текст обращения', 'required', 'rows' => 5, 'autocomplete' => 'off' ] ) !!}

    </div>

</div>

<div class="row margin-top-10">

    <div class="col-md-6">

        {!! Form::label( 'tags', 'Теги', [ 'class' => 'control-label' ] ) !!}
        {!! Form::text( 'tags', \Input::old( 'tags', $ticket->tags->implode( 'text', ',' ) ), [ 'class' => 'form-control input-large', 'data-role' => 'tagsinput', 'autocomplete' => 'off' ] ) !!}

    </div>

    <div class="col-md-6 text-right">
        @if ( isset( $moderate ) )
            <a href="{{ route( 'tickets.moderate.reject', $ticket->id ) }}" class="btn red" data-confirm="Вы уверены, что хотите отклонить заявку?">
                <i class="fa fa-remove"></i>
                Отклонить
            </a>
            <button type="submit" class="btn green">
                <i class="fa fa-check"></i>
                Принять
            </button>
        @else
            <a href="{{ route( 'tickets.cancel', $ticket->id ) }}" class="btn red" data-confirm="Вы уверены, что хотите очистить заявку?">
                <i class="fa fa-remove"></i>
                Очистить
            </a>
            <button type="button" class="btn yellow" data-action="create_another">
                <i class="fa fa-plus"></i>
                Сохранить и создать еще
            </button>
            <button type="submit" class="btn green">
                <i class="fa fa-check"></i>
                Сохранить
            </button>
        @endif
    </div>

</div>

{!! Form::hidden( 'create_another', '0', [ 'id' => 'create_another' ] ) !!}
{!! Form::close() !!}