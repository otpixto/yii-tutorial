@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'catalog.managements.create', 'catalog.managements.export' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-6">
                @if ( \Auth::user()->can( 'catalog.managements.create' ) )
                    <a href="{{ route( 'managements.create' ) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-plus"></i>
                        Добавить УО
                    </a>
                @endif
            </div>
            <div class="col-xs-6 text-right">
                @if ( \Auth::user()->can( 'catalog.managements.export' ) )
                    <a href="?export=1&{{ Request::getQueryString() }}" id="alExcelDownloadButton" class="btn btn-default btn-lg">
                        <i class="fa fa-download"></i>
                        Выгрузить в Excel
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ( \Auth::user()->can( 'catalog.managements.search' ) )

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

    @if ( \Auth::user()->can( 'catalog.managements.show' ) )

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div id="list"></div>

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        function loadManagements ( url )
        {
            $( '#list' ).loading();
            $.ajax({
                url: url || window.location.href,
                method: 'get',
                cache: false,
                success: function ( response )
                {
                    $( '#list' ).html( response );
                }
            });
        };

        $( document )

            .ready( function ()
            {

                loadManagements();

            })

            .on( 'submit', '#search-form', function ( e )
            {
                e.preventDefault();
                $( '#list' ).loading();
                var button = $( this ).find( ':submit' );
                button.attr( 'disabled', 'disabled' ).addClass( 'loading' );

                var href = $('#alExcelDownloadButton').attr('href');
                $('#alExcelDownloadButton').attr('href', href + $( this ).serialize());

                $.ajax({
                    url: $( this ).attr( 'action' ),
                    method: $( this ).attr( 'method' ),
                    cache: false,
                    data: $( this ).serialize(),
                    success: function ( response )
                    {
                        $( '#list' ).html( response );
                        button.removeAttr( 'disabled' ).removeClass( 'loading' );
                    }
                });
            })

            .on( 'click', '.pagination a', function ( e )
            {
                e.preventDefault();
                var url = $( this ).attr( 'href' );
                loadManagements( url );
                window.history.pushState( '', '', url );
            })

            .on( 'click', '[data-load="search"]', function ( e )
            {
                e.preventDefault();
                if ( $( '#search' ).text().trim() == '' )
                {
                    $( '#search' ).loading();
                    $.get( '{{ route( 'managements.search.form' ) }}', window.location.search, function ( response )
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

                        $( '.mask_phone' ).inputmask( 'mask', {
                            'mask': '+7 (999) 999-99-99'
                        });

                        $( '#segment_id' ).selectSegments();

                    });
                }
            })

    </script>

@endsection
