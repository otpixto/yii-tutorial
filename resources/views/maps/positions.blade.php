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

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label' ] ) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    от
                </span>
                <input class="form-control" name="date_from" type="datetime-local" value="{{ $date_from->format( 'Y-m-d\TH:i' ) }}" id="date_from" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
                <span class="input-group-addon">
                    до
                </span>
                <input class="form-control" name="date_to" type="datetime-local" value="{{ $date_to->format( 'Y-m-d\TH:i' ) }}" id="date_to" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
            </div>
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'user_id', 'Сотрудник', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'user_id', $availableUsers, \Request::get( 'user_id' ), [ 'class' => 'select2 form-control', 'placeholder' => 'Сотрудник', 'data-placeholder' => 'Сотрудник', 'autocomplete' => 'off' ] ) !!}
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'history', 'Отображать историю', [ 'class' => 'control-label' ] ) !!}
            {!! Form::checkbox( 'history', 1, \Request::get( 'history' ) ) !!}
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

    <div class="progress hidden" id="loading">
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

        ymaps.ready( mapInit );

        $( document )

            .on( 'submit', '#filter-form', function ( e )
            {
                e.preventDefault();
                getData();
            });

        function getData ( callback )
        {
            $( '#loading' ).removeClass( 'hidden' );
            $( '#map' ).css( 'opacity', 0 );
            myMap.geoObjects.removeAll();
            $.get( '{{ route( 'data.positions' ) }}', {
                user_id: $( '#user_id' ).val(),
                date_from: $( '#date_from' ).val(),
                date_to: $( '#date_to' ).val(),
                history: $( '#history' ).is( ':checked' ) ? 1 : 0
            },
            function ( response )
            {
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
                            }
                            prevData[ position.user_id ] = [ history.lat, history.lon ];
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
                        });
                    });
                    myMap.setBounds( myMap.geoObjects.getBounds(), {
                        checkZoomRange: true
                    });
                    //myMap.setCenter( Object.values( prevData ).pop() );
                }
                $( '#loading' ).addClass( 'hidden' );
                $( '#map' ).css( 'opacity', 1 );
                if ( callback )
                {
                    callback.call( callback );
                }
            });
        };

        function mapInit ()
        {
            myMap = new ymaps.Map( 'map',
                {
                    center: [ 55.76, 37.64 ],
                    zoom: 12
                }
            );
            getData( function ()
            {
            });
        };

    </script>

@endsection