@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal hidden-print' ] ) !!}
    @if ( $providers->count() > 1 )
        <div class="form-group">
            {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label col-md-3' ] ) !!}
            <div class="col-md-6">
                {!! Form::select( 'provider_id', $providers, $provider_id, [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>
    @endif
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-3">
            <div class="input-group date datetimepicker form_datetime bs-datetime">
                {!! Form::text( 'date_from', $date_from->format( 'd.m.Y H:i' ), [ 'class' => 'form-control' ] ) !!}
                <span class="input-group-addon">
                    <button class="btn default date-reset" type="button">
                        <i class="fa fa-times"></i>
                    </button>
                </span>
                <span class="input-group-addon">
                    <button class="btn default date-set" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                </span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group date datetimepicker form_datetime bs-datetime">
                {!! Form::text( 'date_to', $date_to->format( 'd.m.Y H:i' ), [ 'class' => 'form-control' ] ) !!}
                <span class="input-group-addon">
                    <button class="btn default date-reset" type="button">
                        <i class="fa fa-times"></i>
                    </button>
                </span>
                <span class="input-group-addon">
                    <button class="btn default date-set" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label( 'status_code', 'Статус', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            {!! Form::select( 'status_code', $availableStatuses, $status_code, [ 'class' => 'form-control select2', 'placeholder' => '---' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            {!! Form::select( 'management_id', [ null => ' -- выберите из списка -- ' ] + $availableManagements, \Input::get( 'management_id' ), [ 'class' => 'select2 form-control' ] ) !!}
        </div>
    </div>
    <div id="executor_block" class="form-group @if ( ! $management ) hidden @endif">
        {!! Form::label( 'executor_id', 'Исполнитель', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            {!! Form::select( 'executor_id', $executors->count() ? [ null => ' -- выберите из списка -- ' ] + $executors->pluck( 'name', 'id' )->toArray() : [ null => 'Ничего не найдено' ], \Input::get( 'executor_id' ), [ 'class' => 'select2 form-control' ] ) !!}
        </div>
    </div>
    <div id="rate" class="form-group">
        {!! Form::label( 'rate', 'Оценка', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            <div id="rate_slider" class="noUi-danger"></div>
            {!! Form::hidden( 'rate_from', \Input::get( 'rate_from', 1 ), [ 'id' => 'rate_from' ] ) !!}
            {!! Form::hidden( 'rate_to', \Input::get( 'rate_to', 5 ), [ 'id' => 'rate_to' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
            @if ( $ticketManagements->count() && \Auth::user()->can( 'reports.export' ) )
                <a href="?export=1&{{ http_build_query( \Request::except( 'export' ) ) }}" class="btn btn-default">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            @endif
        </div>
    </div>
    {!! Form::close() !!}

    <div class="visible-print title">
        Статистический отчет по заявкам за период с {{ $date_from->format( 'd.m.Y H:i' ) }} по {{ $date_to->format( 'd.m.Y H:i' ) }}
        Исполнитель
        @if ( $management )
            {{ $management->name }}
        @endif
        @if ( $executor )
            {{ $executor->name }}
        @endif
    </div>

    @if ( $ticketManagements->count() )

        {{ $ticketManagements->render() }}

        <table class="table table-striped sortable" id="data">
            <thead>
            <tr>
                <th>
                    № заявки
                </th>
                <th>
                    Дата создания
                </th>
                <th>
                    Адрес заявки
                </th>
                <th>
                    Классификатор
                </th>
                <th>
                    Выполненные работы
                </th>
                <th>
                    Статус заявки \ Выполнено
                </th>
                <th class="text-center">
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
                        @if ( $ticketManagement->ticket->type )
                            @if ( $ticketManagement->ticket->type->parent )
                                <div class="bold">
                                    {{ $ticketManagement->ticket->type->parent->name }}
                                </div>
                            @endif
                            <div>
                                {{ $ticketManagement->ticket->type->name }}
                            </div>
                        @endif
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

            })

            .on( 'change', '#management_id', function ()
            {

                var management_id = $( this ).val();

                if ( management_id )
                {

                    $( '#executor_block' ).removeClass( 'hidden' );
                    $( '#executor_id' ).html(
                        $( '<option>' ).val( '0' ).text( 'Загрузка...' )
                    );

                    $.get( '{{ route( 'managements.executors.search' ) }}', {
                        management_id: management_id
                    }, function ( response )
                    {
                        $( '#executor_id' ).html(
                            $( '<option>' ).val( '0' ).text( response.length ? ' -- выберите из списка -- ' : 'Ничего не найдено' )
                        );
                        $.each( response, function ( i, val )
                        {
                            $( '#executor_id' ).append(
                                $( '<option>' ).val( val.id ).text( val.name )
                            );
                        });
                    });

                }
                else
                {
                    $( '#executor_block' ).addClass( 'hidden' );
                    $( '#executor_id' ).empty().val( '' );
                }

            })

            .on( 'change', '#provider_id', function ()
            {
                $( this ).closest( 'form' ).submit();
            });

    </script>


@endsection