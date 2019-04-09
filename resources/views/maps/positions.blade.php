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

        ymaps.ready(mapInit);

        function mapInit ()
        {
            $.get( '{{ route( 'data.positions' ) }}', function ( response )
            {
                var myMap = new ymaps.Map( 'map',
                    {
                        center: [ 55.76, 37.64 ],
                        zoom: 12
                    }
                );
                if ( response && response.length )
                {
                    var prevData = {};
                    $.each( response, function ( i, position )
                    {
                        var myPlacemark = new ymaps.Placemark( [ position.lat, position.lon ], {
                            iconContent: position.user_name,
                            balloonContent: '<strong>' + position.user_name + '</strong><br>был здесь ' + position.date
                        }, {
                            // Иконка будет зеленой и
                            // растянется под iconContent.
                            preset: 'islands#greenStretchyIcon'
                        });
                        myMap.geoObjects.add( myPlacemark );
                        $.each( position.history, function ( i2, history )
                        {
                            if ( prevData[ position.user_id ] )
                            {
                                var myPolyline = new ymaps.Polyline([
                                    prevData[ position.user_id ],
                                    [ history.lat, history.lon ]
                                ], {
                                    balloonContent: position.user_name
                                });
                                myMap.geoObjects.add( myPolyline );
                                if ( position.lat != history.lat && position.lon != history.lon )
                                {
                                    var myPlacemark = new ymaps.Placemark( [ history.lat, history.lon ], {
                                        iconContent: position.user_name,
                                        balloonContent: '<strong>' + position.user_name + '</strong><br>был здесь ' + history.date
                                    }, {
                                        preset: 'islands#lightBlueDotIcon'
                                    });
                                    myMap.geoObjects.add( myPlacemark );
                                }
                            }
                            prevData[ position.user_id ] = [ history.lat, history.lon ];
                        });
                    });
                    myMap.setBounds( myMap.geoObjects.getBounds(), {
                        checkZoomRange: true
                    });
                    //myMap.setCenter( Object.values( prevData ).pop() );
                }
                $( '#map' ).css( 'opacity', 1 );
                $( '#loading' ).hide();
            });
        };

    </script>

@endsection