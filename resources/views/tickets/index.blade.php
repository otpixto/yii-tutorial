@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'tickets.show', 'tickets.all' ) )

        @if( \Auth::user()->canOne( 'tickets.create', 'tickets.export' ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @if( \Auth::user()->can( 'tickets.create' ) )
                        <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить заявку
                        </a>
                    @endif
                </div>
                <div class="col-xs-6 text-right">
                    @if( \Auth::user()->can( 'tickets.export' ) )
                        <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ( \Auth::user()->can( 'tickets.search' ) )
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
                            @if ( $regions->count() > 1)
                                <div class="row margin-top-10">
                                    <h4 class="col-md-2">
                                        Регион
                                    </h4>
                                    <div class="col-md-3">
                                        {!! Form::select( 'region_id', $regions->toArray(), \Input::get( 'region_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Все', 'id' => 'region_id' ] ) !!}
                                    </div>
                                </div>
                            @endif
                            <div class="row margin-top-10">
                                <h4 class="col-md-2">
                                    Номер заявки
                                </h4>
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    {!! Form::text( 'phone', \Input::get( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
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
                            <div class="margin-top-15">
                                {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
                                @if ( Input::get( 'search' ) )
                                    <a href="{{ route( 'tickets.index' ) }}" class="btn btn-default">
                                        Сбросить фильтр
                                    </a>
                                @endif
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row margin-top-15" id="result">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-md-8">
                        {{ $ticketManagements->render() }}
                    </div>
                    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $ticketManagements->total() }}</b>
                        </span>
                    </div>
                </div>

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="info">
                            <th width="250">
                                 Статус \ Номер заявки \ Оценка
                            </th>
                            <th width="220">
                                Дата и время создания
                            </th>
                            @if ( $field_operator )
                                <th width="150">
                                    Оператор
                                </th>
                            @endif
                            <th width="200">
                                @if ( $field_management )
                                    УО \
                                @endif
                                Исполнитель
                            </th>
                            <th width="300">
                                Классификатор
                                @if ( \Auth::user()->can( 'tickets.works.show' ) )
                                    \ Выполненные работы
                                @endif
                            </th>
                            <th colspan="2">
                                Адрес проблемы \ Заявитель
                            </th>
                        </tr>
                    </thead>
                    {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
                    <tbody id="tickets">
                        <tr id="tickets-new-message" class="hidden">
                            <td colspan="7">
                                <button type="button" class="btn btn-warning btn-block btn-lg" id="tickets-new-show">
                                    Добавлены новые заявки <span class="badge bold" id="tickets-new-count">2</span>
                                </button>
                            </td>
                        </tr>
                    @if ( $ticketManagements->count() )
                        @foreach ( $ticketManagements as $ticketManagement )
                            @include( 'parts.ticket', [ 'ticketManagement' => $ticketManagement ] )
                        @endforeach
                        </tbody>
                        {!! Form::close() !!}
                    @else
                        </tbody>
                    @endif
                </table>

                {{ $ticketManagements->render() }}

                @if ( ! $ticketManagements->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

        @if ( \Auth::user()->can( 'tickets.waybill' ) )
            <div id="controls" style="display: none;">
                {!! Form::open( [ 'url' => route( 'tickets.waybill' ), 'method' => 'get', 'target' => '_blank' ] ) !!}
                {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}
                <button type="submit" class="btn btn-default btn-lg">
                    Распечатать наряд-заказы (<span id="ids-count">0</span>)
                </button>
                {!! Form::close(); !!}
            </div>
        @endif

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <style>
        #controls {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999999;
            background: #2f373e;
            padding: 15px 0;
            text-align: center;
        }
        .alert, .mt-element-ribbon, .note {
            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -19px;
            top: -19px;
        }
        .color-inherit {
            color: inherit;
        }
        .border-left {
            border-left: 2px solid #b71a00 !important;
        }
        .opacity {
            opacity: 0.5;
        }
        .portlet {
            margin-bottom: 0;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
    <script type="text/javascript">

        function checkTicketCheckbox ()
        {
            var ids = [];
            $( '.ticket-checkbox:checked' ).each( function ()
            {
                ids.push( $( this ).val() );
            });
            $( '#ids-count' ).text( ids.length );
            if ( ids.length )
            {
                $( '#controls' ).fadeIn( 300 );
                $( '#ids' ).val( ids.join( ',' ) );
            }
            else
            {
                $( '#controls' ).fadeOut( 300 );
                $( '#ids' ).val( '' );
            }
        };

        $( document )

            .ready( function ()
            {

                $('.date-picker').datepicker({
                    rtl: App.isRTL(),
                    orientation: "left",
                    autoclose: true
                });

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    allowClear: true,
                    ajax: {
                        delay: 450,
                        cache: true,
                        data: function ( term, page )
                        {
                            return {
                                q: term.term,
                                region_id: $( '#region_id' ).val() || null
                            };
                        },
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

                checkTicketCheckbox();

                $( '.mt-multiselect' ).multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

                $( '.customer-autocomplete' ).autocomplete({
                    source: function ( request, response )
                    {
                        var r = {};
                        r.param = this.element[0].name;
                        r.value = request.term;
                        $.getJSON( '{{ route( 'customers.search' ) }}', r, function ( data )
                        {
                            response( data );
                        });
                    },
                    minLength: 2,
                    select: function ( event, ui )
                    {
                        $( this ).trigger( 'change' );
                    }
                });

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            })

            .on( 'change', '.ticket-checkbox', checkTicketCheckbox )

            .on( 'change', '#region_id', function ( e )
            {
                $( '#address_id, #actual_address_id' ).val( '' ).trigger( 'change' );
            });

    </script>
@endsection