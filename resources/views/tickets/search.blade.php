<div class="row margin-top-15 hidden-print">
    <div class="col-xs-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-search"></i>
                    ПОИСК
                </div>
                <div class="tools">
                    <a href="javascript:;" class="{{ ! Input::get( 'search' ) ? 'expand' : 'collapse' }}" data-original-title="Показать\Скрыть" title="Показать\Скрыть"> </a>
                </div>
            </div>
            <div class="portlet-body {{ ! Input::get( 'search' ) ? 'portlet-collapsed' : '' }}">
                {!! Form::open( [ 'method' => 'post', 'class' => 'submit-loading', 'url' => route( 'tickets.filter' ) ] ) !!}
                {!! Form::hidden( 'search', 1 ) !!}
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
                                {!! Form::select( 'address_id', $address, \Input::get( 'address_id' ), [ 'id' => 'address_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'addresses.search' ), 'data-placeholder' => 'Адрес проблемы' ] ) !!}
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
                        Адрес заявителя
                    </h4>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-xs-10">
                                {!! Form::select( 'actual_address_id', $actual_address, \Input::get( 'actual_address_id' ), [ 'id' => 'actual_address_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'addresses.search' ), 'data-placeholder' => 'Адрес заявителя' ] ) !!}
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
                <div style="display: none;" id="additional-search">
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
                    <hr />
                    @if ( $managements->count() > 1 )
                        <div class="row margin-top-10">
                            <h4 class="col-md-2">
                                УО
                            </h4>
                            <div class="col-md-10">
                                {!! Form::select( 'management_id', $managements, \Input::get( 'management_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Все (' . $managements->count() . ')', 'id' => 'management_id' ] ) !!}
                            </div>
                        </div>
                    @endif
                    <div class="row margin-top-10">
                        <h4 class="col-md-2">
                            Исполнитель
                        </h4>
                        <div class="col-md-10">
                            {!! Form::select( 'executor_id', $executors, \Input::get( 'executor_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Все (' . $executors->count() . ')', 'id' => 'executor_id' ] ) !!}
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
            </div>
        </div>
    </div>
</div>