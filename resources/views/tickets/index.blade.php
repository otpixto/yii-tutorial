@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'tickets.show', 'tickets.all' ) )

        @if ( \Auth::user()->can( 'tickets.create' ) || \Auth::user()->can( 'tickets.export' ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @if( \Auth::user()->can( 'tickets.create' ) )
                        <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить заявку
                        </a>
                    @endif
                </div>
                {{--@if ( \Auth::user()->can( 'tickets.export' ) && $ticketManagements->count() )
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
        @endif

        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route( 'tickets.index' ) }}" class="tickets-ajax ticket-tabs btn btn-default @if ( $request->get( 'show', '' ) == '' ) btn-info @endif">
                    <i class="fa fa-list"></i>
                    Все заявки
                </a>
                |
                <a href="?show=not_processed" class="tickets-ajax ticket-tabs btn btn-default @if ( $request->get( 'show', '' ) == 'not_processed' ) btn-info @endif">
                    <i class="fa fa-clock-o"></i>
                    Необработанные заявки
                </a>
                >
                <a href="?show=in_process" class="tickets-ajax ticket-tabs btn btn-default @if ( $request->get( 'show', '' ) == 'in_process' ) btn-info @endif">
                    <i class="fa fa-wrench"></i>
                    Заявки в работе
                </a>
                >
                <a href="?show=completed" class="tickets-ajax ticket-tabs btn btn-default @if ( $request->get( 'show', '' ) == 'completed' ) btn-info @endif">
                    <i class="fa fa-check-circle"></i>
                    Выполненные заявки
                </a>
                >
                <a href="?show=closed" class="tickets-ajax ticket-tabs btn btn-default @if ( $request->get( 'show', '' ) == 'closed' ) btn-info @endif">
                    <i class="fa fa-dot-circle-o"></i>
                    Закрытые заявки
                </a>
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
        #customer_tickets table *, #neighbors_tickets table * {
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
            $( '#tickets' ).loading();
            $.get( url || window.location.href, function ( response )
            {
                $( '#tickets' ).html( response );
            });
        };

        $( document )

            .ready( function ()
            {

                loadTickets();
                checkTicketCheckbox();

            })

            /*.on( 'click', 'a[href].tickets-ajax', function ( e )
            {
                e.preventDefault();
                var url = $( this ).attr( 'href' );
                loadTickets( url );
                window.history.pushState( '', '', url );
            })

            .on( 'click', 'a[href].ticket-tabs', function ( e )
            {
                e.preventDefault();
                $( 'a[href].ticket-tabs' ).removeClass( 'btn-info' );
                $( this ).addClass( 'btn-info' );
            })*/

            .on( 'click', '[data-load="search"]', function ( e )
            {
                e.preventDefault();
                if ( $( '#search' ).text().trim() == '' )
                {
                    $( '#search' ).loading();
                    $.get( '{{ route( 'tickets.search.form' ) }}', function ( response )
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

            .on( 'click', '#segment', function ( e )
            {

                e.preventDefault();

                Modal.create( 'segment-modal', function ()
                {
                    Modal.setTitle( 'Выберите сегмент' );
                    $.get( '{{ route( 'segments.tree' ) }}', function ( response )
                    {
                        var tree = $( '<div></div>' ).attr( 'id', 'segment-tree' );
                        Modal.setBody( tree );
                        tree.treeview({
                            data: response,
                            onNodeSelected: function ( event, node )
                            {
                                $( '#segment_id' ).val( node.id );
                                $( '#segment' ).text( node.text ).removeClass( 'text-muted' );
                            },
                            onNodeUnselected: function ( event, node )
                            {
                                $( '#segment_id' ).val( '' );
                                $( '#segment' ).text( 'Нажмите, чтобы выбрать' ).addClass( 'text-muted' );
                            }
                        });
                    });
                });

            })

            .on( 'change', '.ticket-checkbox', checkTicketCheckbox );

    </script>
@endsection