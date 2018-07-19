@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'id' => 'calendar-form' ] ) !!}
    <div class="row margin-top-10">
        <div class="col-md-4">
            {!! Form::label( 'date', 'Дата', [ 'class' => 'control-label' ] ) !!}
            {!! Form::date( 'date', $date->format( 'Y-m-d' ), [ 'class' => 'form-control', 'required' ] ) !!}
        </div>
        <div class="col-md-8">
            {!! Form::label( 'managements', 'УО', [ 'class' => 'control-label' ] ) !!}
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements" name="managements[]">
                @foreach ( $availableManagements as $management => $arr )
                    <optgroup label="{{ $management }}">
                        @foreach ( $arr as $management_id => $management_name )
                            <option value="{{ $management_id }}">
                                {{ $management_name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row margin-top-10">
        <div class="col-md-6">
            {!! Form::label( 'building_id', 'Адрес', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'building_id', [], \Input::get( 'building_id' ), [ 'id' => 'building_id', 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес' ] ) !!}
        </div>
        <div class="col-md-6">
            {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
            <span id="segment" class="form-control text-muted">
                Нажмите, чтобы выбрать
            </span>
            {!! Form::hidden( 'segment_id', \Input::get( 'segment_id' ), [ 'id' => 'segment_id' ] ) !!}
        </div>
    </div>
    <div class="row margin-top-10">
        <div class="col-md-12">
            {!! Form::submit( 'Показать', [ 'class' => 'btn btn-success' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <div id="calendar" class="margin-top-15"></div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.print.css" rel="stylesheet" type="text/css" media='print' />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/fullcalendar-3.9.0/lib/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.js" type="text/javascript"></script>
    <script src='/assets/global/plugins/fullcalendar-3.9.0/locale-all.js'></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mt-multiselect' ).multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on( 'submit', '#calendar-form', function ( e )
            {

                e.preventDefault();

                $( '#calendar' ).loading();

                $.post( '{{ route( 'tickets.calendar_data' ) }}', $( this ).serialize(), function ( response )
                {

                    $( '#calendar' ).fullCalendar( 'destroy' );
                    $( '#calendar' ).empty();

                    $( '#calendar' ).fullCalendar({
                        locale: 'ru',
                        defaultView: 'month',
                        eventLimit: true,
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay,listDay'
                        },
                        defaultDate: '{{ $date->format( 'Y-m-d' ) }}',
                        validRange: {
                            start: '{{ $beginDate->format( 'Y-m-d' ) }}',
                            end: '{{ $endDate->format( 'Y-m-d' ) }}'
                        },
                        editable: false,
                        changeView: function ( changedView )
                        {
                            console.log( changedView );
                            // do anything with the view that will be changed
                        },
                        events: response.events
                    });

                });

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