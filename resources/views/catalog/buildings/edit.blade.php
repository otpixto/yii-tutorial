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
                        <div class="col-md-3">
                            {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $building->provider_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Поставщик' ] ) !!}
                        </div>
                    @else
                        <div class="col-md-3">
                            {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                            <span class="form-control">
                                {{ $building->provider->name }}
                            </span>
                        </div>
                        {!! Form::hidden( 'provider_id', $building->provider_id ) !!}
                    @endif

                    <div class="col-md-7">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $building->name ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'number', 'Номер', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'number', \Input::old( 'number', $building->number ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-4">
                        {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
                        @if ( $building->segment )
                            <div id="segment_id" data-name="segment_id" data-value="{{ $building->segment->id }}" data-title="{{ $building->segment->getName() }}"></div>
                        @else
                            <div id="segment_id" data-name="segment_id"></div>
                        @endif
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'date_of_construction', 'Дата постройки', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'date_of_construction', \Input::old( 'date_of_construction', \Carbon\Carbon::parse( $building->date_of_construction )->format( 'd.m.Y' ) ), [ 'class' => 'form-control datepicker', 'placeholder' => 'Дата постройки', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'eirts_number', 'Код ЕИРЦ', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'eirts_number', \Input::old( 'eirts_number', $building->eirts_number ), [ 'class' => 'form-control', 'placeholder' => 'Код ЕИРЦ' ] ) !!}
                    </div>

                    <div class="col-md-4">
                        {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'guid', \Input::old( 'guid', $building->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-3">
                        {!! Form::label( 'building_type_id', 'Тип здания', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'building_type_id', $buildingTypes,\Input::old( 'building_type_id', $building->building_type_id ), [ 'class' => 'form-control', 'placeholder' => 'Тип здания' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'total_area', 'Общая площадь', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'total_area', \Input::old( 'total_area', $building->total_area ), [ 'class' => 'form-control', 'placeholder' => 'Общая площадь' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'living_area', 'Жилая площадь', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'living_area', \Input::old( 'living_area', $building->living_area ), [ 'class' => 'form-control', 'placeholder' => 'Жилая площадь' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'room_mask', 'Маска нумерации', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'room_mask', \Input::old( 'room_mask', $building->room_mask ), [ 'class' => 'form-control', 'placeholder' => 'Маска нумерации' ] ) !!}
                    </div>

                    <div class="col-md-1">
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
                        {!! Form::number( 'floor_count', \Input::old( 'floor_count', $building->floor_count ), [ 'class' => 'form-control', 'placeholder' => 'Кол-во этажей' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'room_total_count', 'Всего помещений', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'room_total_count', \Input::old( 'room_total_count', $building->room_total_count ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'first_floor_index', 'Номер первого этажа', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'first_floor_index', \Input::old( 'first_floor_index', $building->first_floor_index ), [ 'class' => 'form-control', 'placeholder' => 'Номер первого этажа' ] ) !!}
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
                                {{ $segment->segmentType->name }}
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

        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>
                                Этаж
                            </th>
                            @for ( $porch = 1; $porch <= $building->porches_count; $porch ++ )
                                <th class="text-center">
                                    Подъезд #{{ $porch }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                    @for( $floor = $building->floor_count; $floor >= 1; $floor -- )
                        <tr @if ( $floor == 1 && ! $building->is_first_floor_living ) class="bg-grey" @endif>
                            <td>
                                Этаж #{{ $floor }}
                            </td>
                            @if ( $floor == 1 && ! $building->is_first_floor_living )
                                <td colspan="{{ $building->porches_count }}" class="text-center">
                                    Нежилой
                                </td>
                            @else
                                @for ( $porch = 1; $porch <= $building->porches_count; $porch ++ )
                                    <td class="text-center">
                                        <div data-floor="{{ $floor }}" data-porch="{{ $porch }}"></div>
                                    </td>
                                @endfor
                            @endif
                        </tr>
                    @endfor
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                {!! Form::open( [ 'url' => route( 'buildings.store.rooms', $building->id ) ] ) !!}
                {!! Form::submit( 'Пересчитать комнаты', [ 'class' => 'btn btn-success btn-lg' ] ) !!}
                {!! Form::close() !!}
            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

    @if ( \Auth::user()->can( 'catalog.buildings.delete' ) )
        {!! Form::model( $building, [ 'method' => 'delete', 'route' => [ 'buildings.destroy', $building->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены, что хотите удалить здание?' ] ) !!}
        {!! Form::submit( 'Удалить здание', [ 'class' => 'btn btn-danger' ] ) !!}
        {!! Form::close() !!}
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        #map {
            height: 600px;
        }
        .btn-room {
            min-width: 40px;
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

                $.get( '{{ route( 'data.buildings.rooms', $building->id ) }}', function ( response )
                {
                    $.each( response, function ( i, room )
                    {
                        $( '[data-floor="' + room.floor + '"][data-porch="' + room.porch + '"]' ).append(
                            $( '<a class="btn btn-sm btn-room">' )
                                .text( room.number )
                                .attr( 'data-room', room.id )
                                .addClass( room.is_technical ? 'btn-warning' : 'btn-info' )
                        );
                    });
                });

                $( '#segment_id' ).selectSegments();

            });

    </script>
@endsection