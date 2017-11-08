@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div id="map"></div>

@endsection

@section( 'css' )
    <style>
        #map {
            height: 600px;
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
                            preset: 'islands#invertedVioletClusterIcons',
                            groupByCoordinates: false,
                            clusterDisableClickZoom: true,
                            clusterHideIconOnBalloonOpen: false,
                            geoObjectHideIconOnBalloonOpen: false,
							gridSize: 80
                        }),
       
                        getPointData = function ( val )
                        {
                            return {
                                balloonContentHeader: '<h3>' + val[0] + '</h3>',
                                balloonContentBody: '<p>Количество заявок: <b>' + val[3] + '</b></p><p>УК: <b>' + val[2].join( ', ' ) + '</b><p>',
                                clusterCaption: val[0]
                            };
                        },
        
                        getPointOptions = function () {
                            return {
                                preset: 'islands#violetIcon'
                            };
                        };

                    $.get( '/data/addresses', function ( response )
                    {
                        $.each( response, function ( address_id, val )
                        {
							var pos = val[1].split( ' ' );
                            clusterer.add( new ymaps.Placemark( [ pos[1], pos[0] ], getPointData(val), getPointOptions()) );
                        });
						myMap.geoObjects.add(clusterer);
						myMap.setBounds(clusterer.getBounds(), {
							checkZoomRange: true
						});
                    });

                });

            });

    </script>

@endsection