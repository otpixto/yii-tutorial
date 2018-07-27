@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal hidden-print' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-3">
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
        <div class="col-xs-3">
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
        {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::select( 'management_id', [ null => ' -- выберите из списка -- ' ] + $availableManagements, \Input::get( 'management_id' ), [ 'class' => 'select2 form-control' ] ) !!}
        </div>
    </div>
    <div id="executor_block" class="form-group @if ( ! $management ) hidden @endif">
        {!! Form::label( 'executor_id', 'Исполнитель', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::select( 'executor_id', $executors->count() ? [ null => ' -- выберите из списка -- ' ] + $executors->pluck( 'name', 'id' )->toArray() : [ null => 'Ничего не найдено' ], \Input::get( 'executor_id' ), [ 'class' => 'select2 form-control' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ( $management || $executor )

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
                        Категория и тип
                    </th>
                    <th>
                        Выполненные работы
                    </th>
                    <th>
                        Статус заявки
                    </th>
                    <th>
                        Дата
                    </th>
                    <th>
                        Оценка
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach ( $ticketManagements as $ticketManagement )
                    <tr>
                        <td>
                            {{ $ticketManagement->getTicketNumber() }}
                        </td>
                        <td>
                            {{ $ticketManagement->created_at->format( 'd.m.Y H:i' ) }}
                        </td>
                        <td>
                            {{ $ticketManagement->ticket->getAddress( true ) }}
                        </td>
                        <td>
                            @if ( $ticketManagement->ticket->type )
                                <div>
                                    {{ $ticketManagement->ticket->type->category->name }}
                                </div>
                                <div>
                                    {{ $ticketManagement->ticket->type->name }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <ol class="list-unstyled">
                                @foreach ( $ticketManagement->services as $service )
                                    <li>
                                        {{ $service->name }}
                                    </li>
                                @endforeach
                            </ol>
                        </td>
                        <td>
                            {{ $ticketManagement->status_name }}
                        </td>
                        <td>
                            {{ $ticketManagement->ticket->completed_at }}
                        </td>
                        <td>
                            {{ $ticketManagement->rate }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            @include( 'parts.error', [ 'error' => 'По Вашему запросу ничего не найдено' ] )
        @endif

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
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
    </style>
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )

            .ready(function()
            {

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

            });

    </script>


@endsection