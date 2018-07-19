@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
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

        var map;
        var currentObject = null;
        var saveButton, deleteButton, cancelButton;

        var options = {
            Point: {
                default: {
                    preset: 'islands#nightDotIcon',
                    draggable: false
                },
                hover: {
                    preset: 'islands#blueDotIcon',
                    draggable: false
                },
                edit: {
                    preset: 'islands#redDotIcon',
                    draggable: true
                }
            },
            Polygon: {
                default: {
                    // Цвет заливки.
                    fillColor: '#337ab7',
                    // Цвет обводки.
                    strokeColor: '#333333',
                    // Ширина обводки.
                    strokeWidth: 1,
                    // Прозрачность
                    fillOpacity: 0.5,
                    strokeOpacity: 0.5
                },
                hover: {
                    // Цвет заливки.
                    fillColor: '#1752b7',
                    // Цвет обводки.
                    strokeColor: '#ff0900',
                    // Ширина обводки.
                    strokeWidth: 2,
                    // Прозрачность
                    fillOpacity: 0.5,
                    strokeOpacity: 1
                },
                edit: {
                    // Курсор в режиме добавления новых вершин.
                    editorDrawingCursor: "crosshair",
                    // Цвет заливки.
                    fillColor: '#00ff00',
                    // Цвет обводки.
                    strokeColor: '#ff0900',
                    // Ширина обводки.
                    strokeWidth: 2,
                    // Прозрачность
                    fillOpacity: 0.5,
                    strokeOpacity: 1
                }
            }
        };

        function startDraw ()
        {
            if ( currentObject ) return;
            var type = $( this ).attr( 'data-draw' );
            var coordinates = $( this ).attr( 'data-coordinates' ).split( ', ' );
            switch ( type )
            {
                case 'Polygon':
                    currentObject = new ymaps.Polygon( [ [
                        [
                            Number( coordinates[ 0 ] ),
                            Number( coordinates[ 1 ] )
                        ]
                    ] ], {
                        changed: true
                    }, options.Polygon.edit );
                    break;
                case 'Point':
                    currentObject = new ymaps.Placemark(
                        [
                            Number( coordinates[ 0 ] ),
                            Number( coordinates[ 1 ] )
                        ],
                        {
                            changed: true
                        }, options.Point.edit
                    );
                    break;
                default:
                    return;
                    break;
            }
            map.geoObjects.add( currentObject );
            currentObject.editor.startDrawing();
            map.balloon.close();
            saveButton.options.set( 'visible', true );
            deleteButton.options.set( 'visible', true );
        };

        function startEdit ( object )
        {
            if ( currentObject ) return;
            currentObject = object;
            currentObject.editor.startEditing();
            map.balloon.close();
            currentObject.options.set( options[ currentObject.geometry.getType() ].edit );
            saveButton.options.set( 'visible', true );
            deleteButton.options.set( 'visible', true );
        };

        function stopEdit ()
        {
            if ( currentObject )
            {
                currentObject.editor.stopEditing();
                currentObject.options.set( options[ currentObject.geometry.getType() ].default );
            }
            currentObject = null;
            saveButton.options.set( 'visible', false );
            deleteButton.options.set( 'visible', false );
            cancelButton.options.set( 'visible', false );
        };

        function cancelEdit ()
        {
            if ( ! currentObject ) return;
            if ( currentObject.properties.get( 'changed' ) )
            {
                var id = currentObject.properties.get( 'id' );
                bootbox.confirm({
                    message: 'Вы уверены, что хотите отменить изменения?',
                    buttons: {
                        confirm: {
                            label: 'Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: 'Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {
                            map.geoObjects.remove( currentObject );
                            stopEdit();
                            if ( id )
                            {
                                $.post( '{{ route( 'zones.load' ) }}',
                                    {
                                        id: id
                                    },
                                    function ( response )
                                    {
                                        if ( response && response.length )
                                        {
                                            createObject( response[ 0 ] );
                                        }
                                    }, 'json' );
                            }
                        }
                    }
                });
            }
            else
            {
                stopEdit();
            }
        };

        function saveObject ()
        {
            if ( ! currentObject ) return;
            var oldName = currentObject.properties.get( 'hintContent' ) || null;
            var id = currentObject.properties.get( 'id' ) || null;
            var type = currentObject.geometry.getType();
            bootbox.prompt({
                title: 'Введите наименование',
                value: oldName,
                callback: function ( newName )
                {
                    if ( newName === null )
                    {
                        cancelEdit();
                    }
                    else if ( newName == '' )
                    {
                        saveObject();
                    }
                    else
                    {
                        currentObject.properties.set( 'hintContent', newName );
                        var coordinates = currentObject.geometry.getCoordinates();
                        var data = {};
                        data.name = newName;
                        data.type = type;
                        data.coordinates = coordinates;
                        if ( id )
                        {
                            data.id = id;
                            $.ajax({
                                url: '/catalog/zones/update',
                                method: 'PUT',
                                data: data
                            }).done( function ( response )
                            {
                                if ( response.success )
                                {
                                    stopEdit();
                                }
                                else if ( response.errors )
                                {
                                    alert( response.errors );
                                }
                            });
                        }
                        else
                        {
                            $.post( '{{ route( 'zones.store' ) }}', data, function ( response )
                            {
                                if ( response.success )
                                {
                                    data.id = response.id;
                                    map.geoObjects.remove( currentObject );
                                    stopEdit();
                                    createObject( data );
                                }
                                else if ( response.errors )
                                {
                                    alert( response.errors );
                                }
                            });
                        }
                    }
                }
            });
        };

        function deleteObject ()
        {
            if ( ! currentObject ) return;
            var id = currentObject.properties.get( 'id' );
            bootbox.confirm({
                message: 'Вы уверены, что хотите удалить объект?',
                buttons: {
                    confirm: {
                        label: 'Да',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: 'Нет',
                        className: 'btn-danger'
                    }
                },
                callback: function ( result )
                {
                    if ( result )
                    {
                        map.geoObjects.remove( currentObject );
                        stopEdit();
                        if ( id )
                        {
                            $.ajax({
                                url: '/catalog/zones/' + id,
                                method: 'DELETE'
                            }).done( function ( response )
                            {
                                if ( ! response.success && response.errors )
                                {
                                    alert( response.errors );
                                }
                            });
                        }
                    }
                }
            });
        };

        function createObject ( data )
        {

            var newGeoObject = new ymaps.GeoObject(
                {
                    geometry: {
                        type: data.type,
                        coordinates: data.coordinates || {}
                    },
                    properties: {
                        hintContent: data.name || null,
                        balloonContentHeader: data.name || null,
                        //balloonContentBody: data.name || null,
                        id: data.id || null,
                        changed: false
                    }
                });

            newGeoObject.options.set( options[ newGeoObject.geometry.getType() ].default );

            newGeoObject.editor.events.add( [ 'vertexdragend', 'edgedragend' ], function ( e )
            {
                newGeoObject.properties.set( 'changed', true );
                cancelButton.options.set( 'visible', true );
            });

            newGeoObject.events.add( 'dragend', function ( e )
            {
                newGeoObject.properties.set( 'changed', true );
                cancelButton.options.set( 'visible', true );
            });

            newGeoObject.events.add( 'mouseenter', function ( e )
            {
                if ( newGeoObject.editor.state._data.editing ) return;
                newGeoObject.options.set( options[ newGeoObject.geometry.getType() ].hover );
            });

            newGeoObject.events.add( 'mouseleave', function ( e )
            {
                if ( newGeoObject.editor.state._data.editing ) return;
                newGeoObject.options.set( options[ newGeoObject.geometry.getType() ].default );
            });

            map.geoObjects.add( newGeoObject );

        };

        function onDataLoad ( data )
        {
            $( '#loading' ).addClass( 'hidden' );
            if ( ! data.length ) return;
            for ( var i in data )
            {
                createObject( data[ i ] );
            }
            setCenter();
        };

        function setCenter ()
        {
            map.setBounds( map.geoObjects.getBounds() );
        };

        function init ()
        {

            map = new ymaps.Map( 'map',
                {
                    center: [55.73, 37.75],
                    zoom: 10,
                    controls: [
                        'zoomControl',
                        'fullscreenControl',
                        'searchControl'
                    ]
                },
                {
                    searchControlProvider: 'yandex#search'
                });

            map.geoObjects.events.add( 'contextmenu', function ( e )
            {
                e.preventDefault();
                var target = e.get( 'target' );
                if ( ! target.editor.state._data.editing )
                {
                    startEdit( target );
                }
                else
                {
                    cancelEdit();
                }
            });

            map.events.add( 'contextmenu', function ( e )
            {
                if ( e._defaultPrevented || currentObject ) return;
                var coordinates = e.get( 'coords' );
                map.balloon.open( coordinates, {
                    contentHeader: 'Добавить объект',
                    contentBody: '<button class="btn btn-default" data-coordinates="' + coordinates.join( ', ' ) + '" data-draw="Polygon">Многоугольник</button> <button class="btn btn-default" data-coordinates="' + coordinates.join( ', ' ) + '" data-draw="Point">Метка</button>',
                    contentFooter: 'Координаты: ' + coordinates.join( ', ' )
                });
            });

            var buttonLayout = ymaps.templateLayoutFactory.createClass( '<button class="btn btn-\{\{ data.class \}\}">\{\{ data.title \}\}</button>' );

            saveButton = new ymaps.control.Button({
                data: {
                    class: 'success',
                    title: 'Сохранить'
                },
                options: {
                    layout: buttonLayout,
                    visible: false
                }
            });

            deleteButton = new ymaps.control.Button({
                data: {
                    class: 'danger',
                    title: 'Удалить'
                },
                options: {
                    layout: buttonLayout,
                    visible: false
                }
            });

            cancelButton = new ymaps.control.Button({
                data: {
                    class: 'warning',
                    title: 'Отменить'
                },
                options: {
                    layout: buttonLayout,
                    visible: false
                }
            });

            map.controls.add( cancelButton, { float: 'right' } );
            map.controls.add( saveButton, { float: 'right' } );
            map.controls.add( deleteButton, { float: 'right' } );

            cancelButton.events.add( 'click', cancelEdit );
            saveButton.events.add( 'click', saveObject );
            deleteButton.events.add( 'click', deleteObject );

            $.post( '{{ route( 'zones.load' ) }}', onDataLoad, 'json' );

            //$( '.ymaps-2-1-56-map-copyrights-promo, .ymaps-2-1-56-copyright' ).remove();

            $( '#map' ).css( 'opacity', 1 );

        };

        ymaps.ready( init );

        $( document )
            .on( 'click', '[data-draw]', startDraw );


    </script>

@endsection