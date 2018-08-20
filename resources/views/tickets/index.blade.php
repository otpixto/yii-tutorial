@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'tickets.show', 'tickets.all' ) )

        <div class="row hidden-print">
            <div class="col-lg-2 col-md-3 col-sm-6">
                @if( \Auth::user()->can( 'tickets.create' ) )
                    <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-block btn-lg tooltips margin-top-10" title="Добавить заявку">
                        <i class="fa fa-plus"></i>
                        Добавить
                    </a>
                @endif
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="input-group margin-top-10">
                    <span class="input-group-addon">#</span>
                    {!! Form::text( 'ticket_id', '', [ 'class' => 'form-control input-lg', 'placeholder' => '', 'id' => 'ticket_id' ] ) !!}
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 btn-group" data-toggle="buttons">
                        <label class="margin-top-10 btn btn-default btn-xs btn-block border-green-jungle">
                            <input type="checkbox" class="toggle tickets-filter" name="overdue_acceptance" value="1" @if ( $request->get( 'overdue_acceptance' ) == 1 ) checked @endif>
                            ПР. ПРИНЯТИЕ
                        </label>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 btn-group" data-toggle="buttons">
                        <label class="margin-top-10 btn btn-default btn-xs btn-block border-green-jungle">
                            <input type="checkbox" class="toggle tickets-filter" name="overdue_execution" value="1" @if ( $request->get( 'overdue_execution' ) == 1 ) checked @endif>
                            ПР. ИСПОЛНЕНИЕ
                        </label>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 btn-group" data-toggle="buttons">
                        <label class="margin-top-10 btn btn-default btn-xs btn-block border-green-jungle">
                            <input type="checkbox" class="toggle tickets-filter" name="dobrodel" value="1" @if ( $request->get( 'dobrodel' ) == 1 ) checked @endif>
                            ДОБРОДЕЛ
                        </label>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 btn-group" data-toggle="buttons">
                        <label class="margin-top-10 btn btn-default btn-xs btn-block border-green-jungle">
                            <input type="checkbox" class="toggle tickets-filter" name="emergency" value="1" @if ( $request->get( 'emergency' ) == 1 ) checked @endif>
                            АВАРИЙНЫЕ
                        </label>
                    </div>
                </div>
            </div>
            {{--@if ( \Auth::user()->can( 'tickets.export' ) )
                <div class="col-xs-6 text-right">
                    @if( $ticketManagements->total() < 1000 )
                        <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                    @else
                        <span class="text-muted small">
                            Для выгрузки уточните критерии поиска
                        </span>
                    @endif
                </div>
            @endif--}}
        </div>

        <div class="row margin-top-15 hidden-print" data-toggle="buttons">
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="created" @if ( $request->get( 'statuses' ) == 'created' ) checked @endif>
                    НЕРАСПРЕД.
                    ({{ $counts->where( 'status_code', 'created' )->count() }})
                </label>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="rejected" @if ( $request->get( 'statuses' ) == 'rejected' ) checked @endif>
                    ОТКЛОНЕННЫЕ
                    ({{ $counts->where( 'status_code', 'rejected' )->count() }})
                </label>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="from_lk" @if ( $request->get( 'statuses' ) == 'from_lk' ) checked @endif>
                    ИЗ ЛК КЛИЕНТА
                    ({{ $counts->where( 'status_code', 'from_lk' )->count() }})
                </label>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="conflict" @if ( $request->get( 'statuses' ) == 'conflict' ) checked @endif>
                    КОНФЛИКТНЫЕ
                    ({{ $counts->where( 'status_code', 'conflict' )->count() }})
                </label>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="confirmation_operator" @if ( $request->get( 'statuses' ) == 'confirmation_operator' ) checked @endif>
                    ЗАВЕРШЕННЫЕ
                    ({{ $counts->where( 'status_code', 'confirmation_operator' )->count() }})
                </label>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 btn-group">
                <label class="margin-top-10 btn btn-default btn-block border-red-pink">
                    <input type="radio" class="toggle tickets-filter" name="statuses" value="confirmation_client" @if ( $request->get( 'statuses' ) == 'confirmation_client' ) checked @endif>
                    ОЖ. ОЦЕНКИ
                    ({{ $counts->where( 'status_code', 'confirmation_client' )->count() }})
                </label>
            </div>
        </div>

        @if ( \Auth::user()->can( 'tickets.search' ) )

            <div class="row margin-top-15 hidden-print">
                <div class="col-xs-12">
                    <div class="portlet box blue-hoki">
                        <div class="portlet-title">
                            <a class="caption" data-load="search" data-toggle="#search">
                                <i class="fa fa-search"></i>
                                ПОИСК
                            </a>
                        </div>
                        <div class="portlet-body hidden" id="search"></div>
                    </div>
                </div>
            </div>

        @endif

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div id="tickets"></div>

            </div>
        </div>

        @if ( \Auth::user()->can( 'tickets.waybill' ) )
            <div id="controls" style="display: none;">
                {!! Form::open( [ 'url' => route( 'tickets.waybill' ), 'method' => 'get', 'target' => '_blank', 'id' => 'form-checkbox' ] ) !!}
                {!! Form::hidden( 'ids', null, [ 'id' => 'ids' ] ) !!}
                <button type="submit" class="btn btn-default btn-lg">
                    Распечатать наряд-заказы (<span id="ids-count">0</span>)
                </button>
                {!! Form::close(); !!}
                <a href="javascript:;" class="text-default" id="cancel-checkbox">
                    отмена
                </a>
            </div>
        @endif

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <style>
        dl, .alert {
            margin: 0px;
        }
        .note {
            margin: 5px 0;
        }
        .d-inline {
            display: inline;
        }
        #customer_tickets table *, #neighbors_tickets table *, #works table * {
            font-size: 12px;
        }
        @media print
        {
            #ticket-services .row {
                border-top: 1px solid #e9e9e9;
            }
            #ticket-services .form-control {
                padding: 0;
                margin: 0;
                border: none;
            }
        }
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
        .border-green-jungle.active {
            background: #26C281 !important;
            color: #fff;
        }
        .border-red-pink.active {
            color: #fff;
            background-color: #E08283 !important;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
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

        function cancelCheckbox ()
        {
            $( '.ticket-checkbox' ).removeAttr( 'checked' );
            checkTicketCheckbox();
        };

        function loadTickets ( url )
        {
            if ( url )
            {
                window.history.pushState( '', '', url );
            }
            $( '#tickets' ).loading();
            $.ajax({
                url: url || window.location.href,
                method: 'get',
                cache: false,
                success: function ( response )
                {
                    $( '#tickets' ).html( response );
                }
            });
        };

        function filterTickets ( e )
        {

            e.preventDefault();
            url = '{{ route( 'tickets.index' ) }}';
            var elements = $( '.tickets-filter:checkbox:checked, .tickets-filter:radio:checked' );
            var url = '{{ route( 'tickets.index' ) }}';
            var filter = [];
            elements.each( function ()
            {
                var key = $( this ).attr( 'name' );
                var val = $( this ).val();
                if ( key && val )
                {
                    filter.push( key + '=' + encodeURIComponent( val ) );
                }
            });

            url += '?' + filter.join( '&' );
            loadTickets( url );

        };

        function clearFilter ()
        {
            $( '.tickets-filter' ).removeAttr( 'checked' ).parent().removeClass( 'active' );
        };

        $( document )

            .ready( function ()
            {

                loadTickets();
                checkTicketCheckbox();

                $( '.tickets-filter:checked' ).parent().addClass( 'active' );

            })

            .on( 'submit', '#search-form', function ( e )
            {
                e.preventDefault();
                $( '#tickets' ).loading();
                var button = $( this ).find( ':submit' );
                button.attr( 'disabled', 'disabled' ).addClass( 'loading' );
                $.ajax({
                    url: $( this ).attr( 'action' ),
                    method: 'post',
                    cache: false,
                    data: $( this ).serialize(),
                    success: function ( response )
                    {
                        var url = '?' + $.param( response );
                        loadTickets( url );
                        button.removeAttr( 'disabled' ).removeClass( 'loading' );
                    }
                });
            })

            .on( 'keypress', '#ticket_id', function ( e )
            {
                if ( e.keyCode == 13 )
                {
                    e.preventDefault();
                    var url = '{{ route( 'tickets.index' ) }}?ticket_id=' + $( this ).val();
                    clearFilter();
                    loadTickets( url );
                }
            })

            .on( 'click', '.pagination a', function ( e )
            {
                e.preventDefault();
                loadTickets( $( this ).attr( 'href' ) );
            })

            .on( 'change', '.tickets-filter', filterTickets )

            .on( 'click', '[data-load="search"]', function ( e )
            {
                e.preventDefault();
                if ( $( '#search' ).text().trim() == '' )
                {
                    $( '#search' ).loading();
                    $.get( '{{ route( 'tickets.search.form' ) }}', window.location.search, function ( response )
                    {
                        $( '#search' ).html( response );
                        $( '.select2' ).select2();
                        $( '.select2-ajax' ).select2({
                            minimumInputLength: 3,
                            minimumResultsForSearch: 30,
                            ajax: {
                                cache: true,
                                type: 'post',
                                delay: 450,
                                data: function ( term )
                                {
                                    var data = {
                                        q: term.term,
                                        provider_id: $( '#provider_id' ).val()
                                    };
                                    var _data = $( this ).closest( 'form' ).serializeArray();
                                    for( var i = 0; i < _data.length; i ++ )
                                    {
                                        if ( _data[ i ].name != '_method' )
                                        {
                                            data[ _data[ i ].name ] = _data[ i ].value;
                                        }
                                    }
                                    return data;
                                },
                                processResults: function ( data, page )
                                {
                                    return {
                                        results: data
                                    };
                                }
                            }
                        });

                        $( '.datetimepicker' ).datetimepicker({
                            isRTL: App.isRTL(),
                            format: "dd.mm.yyyy hh:ii",
                            autoclose: true,
                            fontAwesome: true,
                            todayBtn: true
                        });

                        $('.date-picker').datepicker({
                            rtl: App.isRTL(),
                            orientation: "left",
                            autoclose: true,
                            format: 'dd.mm.yyyy'
                        });

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
                                $.post( '{{ route( 'customers.search' ) }}', r, function ( data )
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

                        $( '#segment_id' ).selectSegments();

                    });
                }
            })

            .on( 'click', '#cancel-checkbox', function ( e )
            {
                e.preventDefault();
                cancelCheckbox();
            })

            .on( 'submit', '#form-checkbox', function ( event )
            {
                setTimeout( cancelCheckbox, 500 );
            })

            .on( 'change', '.ticket-checkbox', checkTicketCheckbox );

    </script>
@endsection