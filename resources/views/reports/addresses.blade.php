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
            {!! Form::text( 'date_from', $date_from->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::text( 'date_to', $date_to->format( 'd.m.Y' ), [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label( 'address_id', 'Адрес', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::select( 'address_id', $address ? $address->pluck( 'name', 'id' ) : [], $address_id, [ 'class' => 'select2-ajax form-control', 'data-ajax--url' => route( 'addresses.search' ), 'data-placeholder' => 'Адрес', 'required', 'autocomplete' => 'off' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ( $address )

        <div class="visible-print title">
            Статистический отчет по заявкам на {{ $address->name }} за период с {{ $date_from->format( 'd.m.Y' ) }} по {{ $date_to->format( 'd.m.Y' ) }}
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
                            Зона
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
                                @foreach ( $ticketManagement->works as $work )
                                    <li>
                                        {{ $work->name }}
                                        [{{ $work->quantity }}]
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
                            {{ $ticketManagement->management->name }}
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        .progress {
            margin-bottom: 0 !important;
        }
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

    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )

            .ready(function()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

            });

    </script>


@endsection