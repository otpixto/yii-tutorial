@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Здания', route( 'buildings.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $building, [ 'method' => 'put', 'route' => [ 'buildings.update', $building->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    @if ( $providers->count() > 1 )
                        <div class="col-md-4">
                            {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::select( 'provider_id', \Input::old( 'provider_id', $building->provider_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Поставщик' ] ) !!}
                        </div>
                    @else
                        <div class="col-md-4">
                            {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                            <span class="form-control">
                                {{ $building->provider->name }}
                            </span>
                        </div>
                        {!! Form::hidden( 'provider_id', $building->provider_id ) !!}
                    @endif

                    <div class="col-md-6">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $building->name ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'guid', \Input::old( 'guid', $building->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-4">
                    {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
                        <span id="segment" class="form-control text-muted">
                            @if ( $building->segment )
                                {{ $building->segment->name }}
                            @else
                                Нажмите, чтобы выбрать
                            @endif
                        </span>
                        {!! Form::hidden( 'segment_id', \Input::old( 'segment_id', $building->segment_id ) ) !!}
                    </div>

                    <div class="col-md-4">
                        {!! Form::label( 'date_of_construction', 'Дата постройки', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'date_of_construction', \Input::old( 'date_of_construction', \Carbon\Carbon::parse( $building->date_of_construction )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'placeholder' => 'Адрес', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                    </div>

                    <div class="col-md-4">
                        {!! Form::label( 'eirts_number', 'Код ЕИРЦ', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'eirts_number', \Input::old( 'eirts_number', $building->eirts_number ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-3">
                        {!! Form::label( 'total_area', 'Общая площадь', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'total_area', \Input::old( 'total_area', $building->total_area ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'living_area', 'Жилая площадь', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'living_area', \Input::old( 'living_area', $building->living_area ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'room_mask', 'Маска нумерации', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'room_mask', \Input::old( 'room_mask', $building->room_mask ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'room_mask', '1-й этаж жилой', [ 'class' => 'control-label' ] ) !!}
                        <label class="form-control">
                            {!! Form::checkbox( 'is_first_floor_living', 1, $building->is_first_floor_living ) !!}
                        </label>
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-3">
                        {!! Form::label( 'porches_count', 'Кол-во подъездов', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'porches_count', \Input::old( 'porches_count', $building->porches_count ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'floor_count', 'Кол-во этажей', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'floor_count', \Input::old( 'floor_count', $building->floor_count ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'room_total_count', 'Всего помещений', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'room_total_count', \Input::old( 'room_total_count', $building->room_total_count ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'first_floor_index', 'Номер первого этажа', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'first_floor_index', \Input::old( 'first_floor_index', $building->first_floor_index ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="{{ route( 'buildings.managements', $building->id ) }}" class="btn btn-default btn-circle">
                            УО
                            <span class="badge">
                                {{ $building->managements()->count()  }}
                            </span>
                        </a>
                    </div>

                </div>

                {!! Form::close() !!}

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th class="text-right" width="50%">
                                Сегмент
                            </th>
                            <th width="50%">
                                Наименование
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach( $segments as $segment )
                        <tr>
                            <td class="text-right text-muted">
                                {{ $segment->type->name }}
                            </td>
                            <td>
                                {{ $segment->name }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ( $building->lon != -1 && $building->lat != -1 )
            <div class="panel panel-default">
                <div class="panel-body">
                    <div id="map"></div>
                </div>
            </div>
        @endif

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        #map {
            height: 600px;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.datepicker' ).datepicker();

                @if ( $building->lon != -1 && $building->lat != -1 )
                    ymaps.ready( function ()
                    {
                        var myMap = new ymaps.Map( 'map', {
                            center: [{{ $building->lat }}, {{ $building->lon }}],
                            zoom: 17,
                            controls: ['zoomControl']
                        }, {
                            searchControlProvider: 'yandex#search'
                        });
                        myMap.geoObjects
                            .add(
                                new ymaps.Placemark( [{{ $building->lat }}, {{ $building->lon }}], {
                                    balloonContent: '{{ $building->name }}'
                                })
                            );
                    });
                @endif

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