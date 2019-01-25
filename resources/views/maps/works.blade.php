@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Карты' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal submit-loading', 'id' => 'filter-form' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label col-xs-4' ] ) !!}
        <div class="col-xs-4">
            {!! Form::select( 'category_id', [ null => '--- Выберите из списка ---' ] + $availableCategories, $category_id, [ 'class' => 'form-control select2' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <div class="progress" id="loading">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
            Загрузка...
        </div>
    </div>

    <div id="map" style="opacity: 0;"></div>

@endsection

@section( 'css' )
    <style>
        #map {
            height: 600px;
        }
        .page-content-row .page-content-col {
            padding-left: 0 !important;
        }
    </style>
@endsection

@section( 'js' )

    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

    <script type="text/javascript">

        var myMap;

        function getData ( category_id )
        {

            $( '#map' ).css( 'opacity', 0 );
            $( '#loading' ).removeClass( 'hidden' );

            $.get( '/data/works-buildings',
                {
                    category_id: category_id || null
                },
                function ( response )
                {
                    if ( response.length )
                    {
                        $.each( response, function ( address_id, val )
                        {
                            clusterer.add( new ymaps.Placemark( val.coors, getPointData( val ), getPointOptions() ) );
                        });
                        myMap.geoObjects.add(clusterer);
                        myMap.setBounds(clusterer.getBounds(), {
                            checkZoomRange: true
                        });
                    }
                    $( '#map' ).css( 'opacity', 1 );
                    $( '#loading' ).addClass( 'hidden' );
                }
            );

        };

        $( document )

            .ready( function()
            {

                ymaps.ready( function ()
                {

                    myMap = new ymaps.Map( 'map', {
                            center: [ 55.751574, 37.573856 ],
                            zoom: 9,
                            controls: ['zoomControl']
                        }, {
                            searchControlProvider: 'yandex#search'
                        }),
                        clusterer = new ymaps.Clusterer({
                            preset: 'islands#nightClusterIcons',
                            groupByCoordinates: false,
                            clusterDisableClickZoom: true,
                            clusterHideIconOnBalloonOpen: false,
                            geoObjectHideIconOnBalloonOpen: false,
                            gridSize: 80
                        }),

                        getPointData = function ( val )
                        {
                            var balloonContentBody = '';
                            for ( var i in val.works )
                            {
                                balloonContentBody += '' +
                                    '<div class="panel panel-default">' +
                                        '<div class="panel-heading">' +
                                            ( val.works[ i ].category || '<span class="text-danger">Не указана категория</span>' ) +
                                        '</div>' +
                                        '<div class="panel-body">' +
                                            '<div>Включение по плану: ' + val.works[ i ].time_end + '</div>' +
                                            '<div>Организация: ' + val.works[ i ].management + '</div>' +
                                            '<div>' + val.works[ i ].composition + '</div>' +
                                            '<hr />' +
                                            '<a href="' + val.works[ i ].url + '">Перейти <i class="fa fa-chevron-right"></i></a>' +
                                        '</div>' +
                                    '</div>';
                            }
                            return {
                                balloonContentHeader: val.building_name,
                                balloonContentBody: balloonContentBody,
                                clusterCaption: val.building_name
                            };
                        },

                        getPointOptions = function () {
                            return {
                                preset: 'islands#nightDotIcon'
                            };
                        };

                    $( '.ymaps-2-1-56-map-copyrights-promo, .ymaps-2-1-56-copyright' ).remove();

                    getData( "{{ $category_id }}" );

                });

            });

    </script>

@endsection