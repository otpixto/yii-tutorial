@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'tickets.moderate' ) )

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div id="tickets"></div>

            </div>
        </div>

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
            background: #0c4270;
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
                    LoadComments();
                }
            });
        };

        function setExecutor ( ticket_management_id )
        {
            $.get( '{{ route( 'tickets.executor.select' ) }}',
                {
                    ticket_management_id: ticket_management_id
                },
                function ( response )
                {
                    Modal.createSimple( 'Назначить исполнителя', response, 'executor' );
                });
        };

        function postponed ( ticket_id )
        {
            $.get( '{{ route( 'tickets.postponed' ) }}',
                {
                    ticket_id: ticket_id
                },
                function ( response )
                {
                    Modal.createSimple( 'Отложить заявку', response, 'postponed' );
                });
        };

        function filterTickets ( e )
        {

            if ( e )
            {
                e.preventDefault();
            }

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

        function LoadComments ()
        {

            var ids = [];
            $( '[data-ticket-comments]' ).each( function ()
            {
                var id = $( this ).attr( 'data-ticket-comments' );
                if ( ids.indexOf( id ) == -1 )
                {
                    ids.push( id );
                }
            });

            $.post( '{{ route( 'tickets.comments' ) }}', {
                ids: ids
            }, function ( response )
            {
                $.each( response, function ( id, comments )
                {
                    $( '[data-ticket-comments="' + id + '"]' )
                        .removeClass( 'hidden' )
                        .find( '.comments' )
                        .html( comments );
                });
            });

        };

        $( document )

            .ready( function ()
            {

                loadTickets();
                checkTicketCheckbox();

                $( '.tickets-filter:checked' ).parent().addClass( 'active' );

                $( '#scheduled-tickets' ).pulsate({
                    color: '#bf1c56'
                });

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
                    $( this ).attr( 'disabled', 'disabled' ).addClass( 'loading' );
                    var url = '/tickets/' + $( this ).val();
                    window.location.href = url;
                    //clearFilter();
                    //loadTickets( url );
                }
            })

            .on( 'click', '.pagination a', function ( e )
            {
                e.preventDefault();
                loadTickets( $( this ).attr( 'href' ) );
            })

            .on( 'click', 'label.radio.active', function ( e )
            {
                e.preventDefault();
                e.stopPropagation();
                $( this ).removeClass( 'active' ).find( ':radio' ).prop( 'checked', false );
                filterTickets( e );
            })

            .on( 'click', '#filter-clear', function ( e )
            {
                $( '#search' ).empty().addClass( 'hidden' );
                loadTickets( '{{ route( 'tickets.index' ) }}' );
            })

            .on( 'change', '.tickets-filter', filterTickets )

            .on( 'change', '#vendor_id', function ( e )
            {
                if ( $( this ).val() )
                {
                    $( '.vendor' ).removeClass( 'hidden' );
                }
                else
                {
                    $( '.vendor' ).addClass( 'hidden' );
                }
            })

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