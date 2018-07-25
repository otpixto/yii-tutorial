@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Карты' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

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

        $( document )
            .ready(function()
            {

                ymaps.ready(function () {
                    var myMap = new ymaps.Map('map', {
                            center: [55.751574, 37.573856],
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
                            for ( var i in val.tickets )
                            {
                                balloonContentBody += '' +
                                    '<div class="panel panel-default">' +
                                        '<div class="panel-heading">' +
                                            ( val.tickets[ i ].type || '<span class="text-danger">Не указан классификатор</span>' ) +
                                        '</div>' +
                                        '<div class="panel-body">' +
                                            '<div>Организация: ' + val.tickets[ i ].management + '</div>' +
                                            '<div>' + val.tickets[ i ].text + '</div>' +
                                            '<hr />' +
                                            '<a href="' + val.tickets[ i ].url + '">Перейти <i class="fa fa-chevron-right"></i></a>' +
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

                    $.get( '/data/buildings', function ( response )
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
                    });

                });

            });

    </script>

@endsection