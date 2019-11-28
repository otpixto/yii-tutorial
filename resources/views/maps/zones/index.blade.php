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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
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

    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&apikey={{auth()->user()->providers()->first()->yandex_key ?? ''}}" type="text/javascript"></script>

    <script type="text/javascript">

        var map;

        @if ( \Auth::user()->can( 'maps.zones.edit' ) )
            var currentObject = null;
            var saveButton, deleteButton, cancelButton, centerButton, listBox, listBoxItems;
        @endif

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
                    // Курсор в режиме добавления новых вершин.
                    editorDrawingCursor: 'crosshair',
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
                    strokeWidth: 2,
                    fillOpacity: 0.8,
                    strokeOpacity: 1
                },
                edit: {
                    strokeWidth: 5,
                    fillOpacity: 1,
                    strokeOpacity: 1
                },
                draw: {
                    // Курсор в режиме добавления новых вершин.
                    editorDrawingCursor: 'crosshair',
                    // Цвет заливки.
                    fillColor: '#ff0000',
                    // Цвет обводки.
                    strokeColor: '#000000',
                    strokeWidth: 3,
                    fillOpacity: 0.6,
                    strokeOpacity: 1
                }
            }
        };

        @if ( \Auth::user()->can( 'maps.zones.edit' ) )

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
                            id: null,
                            changed: true
                        }, options.Polygon.draw );
                        break;
                    case 'Point':
                        currentObject = new ymaps.Placemark(
                            [
                                Number( coordinates[ 0 ] ),
                                Number( coordinates[ 1 ] )
                            ],
                            {
                                id: null,
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
                cancelButton.options.set( 'visible', true );
                listBox.options.set( 'visible', false );
                setCenter();
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
                cancelButton.options.set( 'visible', true );
                listBox.options.set( 'visible', false );
                setCenter();
            };

            function stopEdit ()
            {
                if ( currentObject )
                {
                    currentObject.editor.stopEditing();
                    var defaultOptions = currentObject.properties.get( 'defaultOptions' );
                    var id = currentObject.properties.get( 'id' );
                    if ( id && defaultOptions )
                    {
                        currentObject.options.set( defaultOptions );
                    }
                    currentObject = null;
                }
                saveButton.options.set( 'visible', false );
                deleteButton.options.set( 'visible', false );
                cancelButton.options.set( 'visible', false );
                listBox.options.set( 'visible', true );
            };

            function cancelEdit ()
            {
                if ( ! currentObject ) return;
                if ( currentObject.properties.get( 'changed' ) )
                {
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
                                reloadObject( currentObject );
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
                var id = currentObject.properties.get( 'id' ) || null;

                $.get( id ? '/maps/zones/' + id + '/edit' : '/maps/zones/create', {
                   id: id,
                   type:  currentObject.geometry.getType()
                }, function ( response )
                {
                    var coordinates = currentObject.geometry.getCoordinates();
                    Modal.createSimple( 'Сохранить объект', response, 'geometry' );
                    $( '[data-id="geometry"] [name="coordinates"]' ).val( JSON.stringify( coordinates ) );
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
                                    url: '/maps/zones/' + id,
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

        @endif

        function reloadObject ( object )
        {

            var id = object.properties.get( 'id' ) || null;

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
                            map.geoObjects.remove( object );
                            createObject( response[ 0 ] );
                        }
                    }, 'json' );
            }
            else
            {
                map.geoObjects.remove( object );
            }
        };

        function createObject ( data )
        {

            if ( data.type == 'Point' )
            {
                var defaultOptions = {
                    preset: data.preset || 'islands#nightDotIcon',
                    draggable: false
                };
            }
            else
            {
                var defaultOptions = {
                    // Цвет заливки.
                    fillColor: data.fillColor || '#337ab7',
                    // Цвет обводки.
                    strokeColor: data.strokeColor || '#333333',
                    // Ширина обводки.
                    strokeWidth: 1,
                    // Прозрачность
                    fillOpacity: 0.5,
                    strokeOpacity: 0.5
                };
            }

            var newGeoObject = new ymaps.GeoObject(
                {
                    geometry: {
                        type: data.type,
                        coordinates: data.coordinates || {}
                    },
                    properties: {
                        hintContent: data.name || null,
                        balloonContentHeader: data.management_name || null,
                        balloonContentBody: data.name || null,
                        id: data.id || null,
                        management_id: data.management_id || null,
                        changed: false,
                        defaultOptions: defaultOptions
                    }
                });

            newGeoObject.options.set( defaultOptions );

            @if ( \Auth::user()->can( 'maps.zones.edit' ) )
                newGeoObject.editor.events.add( [ 'vertexdragend', 'edgedragend' ], function ( e )
                {
                    newGeoObject.properties.set( 'changed', true );
                });
                newGeoObject.events.add( 'dragend', function ( e )
                {
                    newGeoObject.properties.set( 'changed', true );
                });
            @endif

            newGeoObject.events.add( 'mouseenter', function ( e )
            {
                var target = e.get( 'target' );
                if ( target.editor.state._data.editing ) return;
                target.options.set( options[ target.geometry.getType() ].hover );
            });

            newGeoObject.events.add( 'mouseleave', function ( e )
            {
                var target = e.get( 'target' );
                if ( target.editor.state._data.editing ) return;
                target.options.set( target.properties.get( 'defaultOptions' ) );
            });

            map.geoObjects.add( newGeoObject );

        };

        function onDataLoad ( data )
        {
            $( '#loading' ).addClass( 'hidden' );
            if ( ! data.length ) return;
            var listBoxItems = [];
            var used = {};
            for ( var i in data )
            {
                createObject( data[ i ] );
                if ( ! used[ data[ i ].management_id ] )
                {
                    used[ data[ i ].management_id ] = 1;
                    listBoxItems.push({
                        id: data[ i ].management_id,
                        name: data[ i ].management_name
                    });
                }
            }
            listBoxItems
                .sort( function ( a, b )
                {
                    var x = a.name.toLowerCase();
                    var y = b.name.toLowerCase();
                    return x < y ? -1 : x > y ? 1 : 0;
                });
            listBoxItems = listBoxItems
                .map( function ( el )
                {
                    return new ymaps.control.ListBoxItem({
                        data: {
                            content: el.name,
                            id: el.id
                        },
                        state: {
                            selected: true
                        }
                    });
                });
            setCenter();
            listBox = new ymaps.control.ListBox({
                data: {
                    content: 'Выберите УО'
                },
                items: listBoxItems,
                state: {
                    expanded: false,
                    filters: listBoxItems.reduce(function(filters, filter) {
                        filters[ filter.data.get( 'id' ) ] = filter.isSelected();
                        return filters;
                    }, {})
                }
            });
            map.controls.add( listBox, { float: 'right' } );
            // Добавим отслеживание изменения признака, выбран ли пункт списка.
            listBox.events.add(['select', 'deselect'], function ( e )
            {
                var listBoxItem = e.get( 'target' );
                var filters = ymaps.util.extend( {}, listBox.state.get( 'filters' ) );
                filters[ listBoxItem.data.get( 'id' ) ] = listBoxItem.isSelected();
                listBox.state.set( 'filters', filters );
                map.geoObjects.each( function ( geoObject )
                {
                    geoObject.options.set( 'visible', filters[ geoObject.properties.get( 'management_id' ) ] );
                });
            });
            listBox.events.add( 'mouseenter', function ( e )
            {
                var listBoxItem = e.get( 'target' );
                if ( listBoxItem.getParent() == listBox )
                {
                    map.geoObjects.each( function ( geoObject )
                    {
                        if ( geoObject.properties.get( 'management_id' ) == listBoxItem.data.get( 'id' ) && ! geoObject.editor.state._data.editing )
                        {
                            geoObject.options.set( options[ geoObject.geometry.getType() ].hover );
                        }
                    });
                }
            });
            listBox.events.add( 'mouseleave', function ( e )
            {
                var listBoxItem = e.get( 'target' );
                if ( listBoxItem.getParent() == listBox )
                {
                    map.geoObjects.each( function ( geoObject )
                    {
                        if ( geoObject.properties.get( 'management_id' ) == listBoxItem.data.get( 'id' ) && ! geoObject.editor.state._data.editing )
                        {
                            geoObject.options.set( geoObject.properties.get( 'defaultOptions' ) );
                        }
                    });
                }
            });
        };

        function setCenter ()
        {
            if ( currentObject )
            {
                if ( currentObject.geometry.getType() == 'Point' )
                {
                    console.log( currentObject.geometry.getCoordinates() );
                    map.setCenter( currentObject.geometry.getCoordinates() );
                }
                else
                {
                    var bounds = currentObject.geometry.getBounds();
                    if ( bounds[ 0 ][ 0 ] == bounds[ 1 ][ 0 ] && bounds[ 0 ][ 1 ] == bounds[ 1 ][ 1 ] )
                    {
                        map.setCenter( bounds[ 0 ] );
                    }
                    else
                    {
                        map.setBounds( bounds );
                    }
                }
            }
            else
            {
                map.setBounds( map.geoObjects.getBounds() );
            }
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

            @if ( \Auth::user()->can( 'maps.zones.edit' ) )

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
                        class: 'default',
                        title: 'X'
                    },
                    options: {
                        layout: buttonLayout,
                        visible: false
                    }
                });

                centerButton = new ymaps.control.Button({
                    data: {
                        class: 'default',
                        title: 'Отцентровать'
                    },
                    options: {
                        layout: buttonLayout,
                        visible: true
                    }
                });

                map.controls.add( centerButton );

                map.controls.add( saveButton, { float: 'right' } );
                map.controls.add( deleteButton, { float: 'right' } );
                map.controls.add( cancelButton, { float: 'right' } );

                saveButton.events.add( 'click', saveObject );
                deleteButton.events.add( 'click', deleteObject );
                cancelButton.events.add( 'click', cancelEdit );
                centerButton.events.add( 'click', setCenter );

            @endif

            $.post( '{{ route( 'zones.load' ) }}', onDataLoad, 'json' );

            //$( '.ymaps-2-1-56-map-copyrights-promo, .ymaps-2-1-56-copyright' ).remove();

            $( '#map' ).css( 'opacity', 1 );

        };

        ymaps.ready( init );

        $( document )

        @if ( \Auth::user()->can( 'maps.zones.edit' ) )
            .keyup( function ( e )
            {
                if ( e.keyCode == 27 )
                {
                    cancelEdit();
                }
            })

            .on( 'click', '[data-draw]', startDraw )

        @endif
            .on ( 'success', '#form-geometry', function ( e, response )
            {
                if ( response && response.success && currentObject )
                {
                    if ( response.id )
                    {
                        currentObject.properties.set( 'id', response.id );
                    }
                    reloadObject( currentObject );
                }
            });

    </script>

@endsection
