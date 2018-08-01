@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'works.show', 'works.all' ) )

        @if( \Auth::user()->canOne( 'works.create', 'works.export' ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @can( 'works.create' )
                        <a href="{{ route( 'works.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить сообщение
                        </a>
                    @endcan
                </div>
                <div class="col-xs-6 text-right">
                    @can( 'works.export' )
                        <a href="?export=data&{{ Request::getQueryString() }}" class="btn btn-default btn-lg hidden">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                        <a href="?export=report&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Отчет
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        @if ( \Auth::user()->can( 'works.search' ) )

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

                <div id="works"></div>

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <style>
        .alert {
            margin-bottom: 0;
        }
        .mt-element-ribbon {

            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -18px;
            top: -18px;
        }
        .color-inherit {
            color: inherit;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
    <script type="text/javascript">

        function loadWorks ( url )
        {
            $( '#works' ).loading();
            $.ajax({
                url: url || window.location.href,
                method: 'get',
                cache: false,
                success: function ( response )
                {
                    $( '#works' ).html( response );
                }
            });
        };

        $( document )

            .ready( function ()
            {

                loadWorks();

            })

            .on( 'submit', '#search-form', function ( e )
            {
                e.preventDefault();
                $( '#works' ).loading();
                var button = $( this ).find( ':submit' );
                button.attr( 'disabled', 'disabled' ).addClass( 'loading' );
                $.ajax({
                    url: $( this ).attr( 'action' ),
                    method: 'post',
                    cache: false,
                    data: $( this ).serialize(),
                    success: function ( response )
                    {
                        $( '#works' ).html( response );
                        button.removeAttr( 'disabled' ).removeClass( 'loading' );
                    }
                });
            })

            .on( 'click', '.pagination a', function ( e )
            {
                e.preventDefault();
                var url = $( this ).attr( 'href' );
                loadWorks( url );
                window.history.pushState( '', '', url );
            })

            .on( 'click', '[data-load="search"]', function ( e )
            {
                e.preventDefault();
                if ( $( '#search' ).text().trim() == '' )
                {
                    $( '#search' ).loading();
                    $.get( '{{ route( 'works.search.form' ) }}', function ( response )
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

                    });
                }
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

            });

    </script>
@endsection