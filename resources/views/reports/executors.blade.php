@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'hidden-print submit-loading' ] ) !!}

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
            {!! Form::label( 'status_code', 'Статус', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'status_code', $availableStatuses, $status_code, [ 'class' => 'form-control select2', 'placeholder' => '---' ] ) !!}
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'managements', 'УО', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'managements[]', $availableManagements, Request::get( 'managements' ), [ 'class' => 'form-control select2', 'required', 'multiple', 'id' => 'managements' ] ) !!}
        </div>
    </div>

    <div id="executor_block" class="row margin-bottom-15 @if ( ! count( Request::get( 'managements', [] ) ) ) hidden @endif">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'executors', 'Исполнитель', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'executors[]', [], Request::get( 'executors' ), [ 'class' => 'form-control select2', 'multiple', 'id' => 'executors' ] ) !!}
        </div>
    </div>

    <div id="rate" class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'rate', 'Оценка', [ 'class' => 'control-label' ] ) !!}
            <div id="rate_slider" class="noUi-danger"></div>
            {!! Form::hidden( 'rate_from', \Input::get( 'rate_from', 1 ), [ 'id' => 'rate_from' ] ) !!}
            {!! Form::hidden( 'rate_to', \Input::get( 'rate_to', 5 ), [ 'id' => 'rate_to' ] ) !!}
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-xs-offset-3 col-xs-9">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
            @if ( $ticketManagements && \Auth::user()->can( 'reports.export' ) )
                <a href="{{ Request::fullUrl() }}&export=1" class="btn btn-default">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            @endif
        </div>
    </div>
    {!! Form::close() !!}

    <div class="visible-print title">
        Статистический отчет по заявкам за период с {{ $date_from->format( 'd.m.Y H:i' ) }} по {{ $date_to->format( 'd.m.Y H:i' ) }}
    </div>

    @if ( $ticketManagements )

        @if ( $ticketManagements->count() )

            {{ $ticketManagements->render() }}

            <table class="table table-striped sortable" id="data">
                <thead>
                <tr>
                    <th width="5%">
                        № заявки
                    </th>
                    <th width="5%">
                        Дата создания
                    </th>
                    <th width="20%">
                        Адрес заявки
                    </th>
                    <th width="25%">
                        Текст заявки
                    </th>
                    <th width="20%">
                        Выполненные работы
                    </th>
                    <th width="20%">
                        Статус заявки \ Выполнено
                    </th>
                    <th width="5%" class="text-center">
                        Оценка
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach ( $ticketManagements as $ticketManagement )
                    <tr>
                        <td>
                            <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}">
                                {{ $ticketManagement->ticket->id }}
                            </a>
                        </td>
                        <td>
                            {{ $ticketManagement->created_at->format( 'd.m.Y H:i' ) }}
                        </td>
                        <td>
                            {{ $ticketManagement->ticket->getAddress( true ) }}
                        </td>
                        <td>
                            {{ $ticketManagement->ticket->text }}
                        </td>
                        <td>
                            <ol class="list-unstyled">
                                @forelse( $ticketManagement->services as $service )
                                    <li>
                                        {{ $service->name }}
                                    </li>
                                @empty
                                    -
                                @endforelse
                            </ol>
                        </td>
                        <td>
                            <div>
                                {{ $ticketManagement->status_name }}
                            </div>
                            <div>
                                {{ $ticketManagement->ticket->completed_at }}
                            </div>
                        </td>
                        <td class="text-center">
                            {{ $ticketManagement->rate }}
                        </td>
                    </tr>
                    <tr data-ticket-comments="{{ $ticketManagement->ticket->id }}">
                        <td colspan="8">
                            @if ( $ticketManagement->ticket->status_code == 'waiting' && ! empty( $ticketManagement->ticket->postponed_comment ) )
                                <div class="note note-warning">
                                    <span class="small text-muted">Комментарий к отложенной заявке:</span>
                                    {{ $ticketManagement->ticket->postponed_comment }}
                                </div>
                            @endif
                            @if ( isset( $ticketManagement ) && $ticketManagement->rate_comment )
                                <div class="note note-danger">
                                    <span class="small text-muted">Комментарий к оценке:</span>
                                    {{ $ticketManagement->rate_comment }}
                                </div>
                            @endif
                            @if ( \Auth::user()->can( 'tickets.comments' ) && $ticketManagement->ticket->comments->count() )
                                <div class="text-center hidden-print">
                                    <a class="text-primary small bold" data-toggle="#tickets-comments-{{ $ticketManagement->id }}">
                                        Показать \ скрыть комментарии ({{ $ticketManagement->ticket->comments->count() }})
                                    </a>
                                </div>
                                <div class="note note-info hidden" id="tickets-comments-{{ $ticketManagement->id }}">
                                    @include( 'parts.comments', [ 'origin' => $ticketManagement->ticket, 'comments' => $ticketManagement->ticket->comments ] )
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $ticketManagements->render() }}

        @else
            @include( 'parts.error', [ 'error' => 'По Вашему запросу ничего не найдено' ] )
        @endif

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/global/plugins/nouislider/nouislider.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/global/plugins/nouislider/nouislider.pips.css" rel="stylesheet" type="text/css" />
    <style>
        @media print {
            td, th {
                font-size: 9px !important;
            }
            .breadcrumbs {
                display: none;
            }
            .title {
                font-weight: bold;
                margin: 10px 0;
            }
        }
        .note {
            margin: 5px 0;
        }
        .noUi-handle .noUi-tooltip {
            opacity: 0.5;
        }
    </style>
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/nouislider/wNumb.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/nouislider/nouislider.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        function getExecutors ( selected )
        {
            var managements = $( '#managements' ).val();
            $( '#executors' ).empty();
            if ( managements )
            {
                $( '#executor_block' ).removeClass( 'hidden' );
                $.get( '{{ route( 'managements.executors.search' ) }}', {
                    managements: managements
                }, function ( response )
                {
                    $.each( response, function ( i, executor )
                    {
                        $( '#executors' ).append(
                            $( '<option>' ).val( executor.id ).text( executor.name )
                        );
                    });
                    if ( selected )
                    {
                        $( '#executors' ).val( selected ).trigger( 'change' );
                    }
                });
            }
            else
            {
                $( '#executor_block' ).addClass( 'hidden' );
            }
        };

        $( document )

            .ready(function()
            {

                var rateSlider = function() {
                    var tooltipSlider = document.getElementById('rate_slider');

                    noUiSlider.create(tooltipSlider, {
                        start: [1, 5],
                        connect: true,
                        step: 1,
                        range: {
                            'min': 1,
                            'max': 5
                        }
                    });

                    var tipHandles = tooltipSlider.getElementsByClassName('noUi-handle'),
                        tooltips = [];

                    // Add divs to the slider handles.
                    for ( var i = 0; i < tipHandles.length; i++ ){
                        tooltips[i] = document.createElement( 'div' );
                        tipHandles[i].appendChild(tooltips[i]);
                    }

                    // Add a class for styling
                    tooltips[1].className += 'noUi-tooltip';
                    // Add additional markup
                    tooltips[1].innerHTML = '<span class="small text-muted">Оценка: </span><strong></strong>';
                    // Replace the tooltip reference with the span we just added
                    tooltips[1] = tooltips[1].getElementsByTagName( 'strong' )[0];

                    // Add a class for styling
                    tooltips[0].className += 'noUi-tooltip';
                    // Add additional markup
                    tooltips[0].innerHTML = '<span class="small text-muted">Оценка: </span><strong></strong>';
                    // Replace the tooltip reference with the span we just added
                    tooltips[0] = tooltips[0].getElementsByTagName( 'strong' )[0];

                    tooltipSlider.noUiSlider.set( [ $( '#rate_from' ).val(), $( '#rate_to' ).val() ] );

                    // When the slider changes, write the value to the tooltips.
                    tooltipSlider.noUiSlider.on('update', function( values, handle ){
                        tooltips[handle].innerHTML = Number( values[handle] );
                        if ( handle == 0 )
                        {
                            $( '#rate_from' ).val( Number( values[handle] ) );
                        }
                        else
                        {
                            $( '#rate_to' ).val( Number( values[handle] ) );
                        }
                    });
                };

                rateSlider();

                $( '.datetimepicker' ).datetimepicker({
                    isRTL: App.isRTL(),
                    format: "dd.mm.yyyy hh:ii",
                    autoclose: true,
                    fontAwesome: true,
                    todayBtn: true
                });

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

                getExecutors( '{{ implode( ',', Request::get( 'executors', [] ) ) }}'.split( ',' ) );

            })

            .on( 'change', '#managements', getExecutors )

            .on( 'change', '#provider_id', function ()
            {
                $( this ).closest( 'form' ).submit();
            });

    </script>


@endsection