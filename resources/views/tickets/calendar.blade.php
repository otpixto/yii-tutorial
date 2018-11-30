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
        <div class="col-md-3">
            {!! Form::label( 'date', 'Дата', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'date', $date, [ 'class' => 'form-control monthpicker', 'required' ] ) !!}
        </div>
        <div class="col-md-9">
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
            <div id="segment_id" data-name="segments[]"></div>
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
    <link href="/assets/global/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.print.css" rel="stylesheet" type="text/css" media='print' />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <style>
        .ui-monthpicker {
            margin-top: 18px;
        }
        .ui-monthpicker .ui-datepicker-month {
            display: none;
        }
        .ui-monthpicker td span {
            padding: 5px;
            cursor: pointer;
            text-align: center;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-ui.rus.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-ui/jquery-monthpicker.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/fullcalendar-3.9.0/lib/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/fullcalendar-3.9.0/fullcalendar.js" type="text/javascript"></script>
    <script src='/assets/global/plugins/fullcalendar-3.9.0/locale-all.js'></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script type="text/javascript">

        function parseDate ( date )
        {
            var d = new Date();
            d.setTime( Date.parse( date ) );
            return d;
        };

        $( document )

            .ready( function ()
            {

                $( '.monthpicker' ).monthpicker({ dateFormat: 'mm.yy' });

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

                $( '#segment_id' ).selectSegments();

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
                        eventLimit: true,
                        header: {
                            //left: 'prev,next',
                            center: 'title',
                            right: 'month,agendaWeek,agendaDay'
                        },
                        /*validRange: {
                            start: response.beginDate,
                            end: response.endDate
                        },*/
                        defaultDate: response.beginDate,
                        editable: false,
                        dayClick: function ( date, event, view )
                        {
                            if ( view.name != 'agendaDay' )
                            {
                                $( '#calendar' ).fullCalendar( 'changeView', 'agendaDay', date );
                            }
                        },
                        events: response.events
                    });

                    $( '#calendar' ).fullCalendar( 'changeView', 'agendaDay', new Date() );

                });

            });

    </script>
@endsection