@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Заявители', route( 'customers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.customers.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $customer, [ 'method' => 'put', 'route' => [ 'customers.update', $customer->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-3">
                        {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'region_id', $regions->pluck( 'name', 'id' ), \Input::old( 'region_id', $customer->region_id ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Регион' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'actual_address_id', 'Адрес проживания', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'actual_address_id', $customer->actualAddress ? $customer->actualAddress->pluck( 'name', 'id' ) : [], $customer->actual_address_id, [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проживания', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес проживания', 'data-allow-clear' => true, 'required' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'actual_flat', 'Квартира', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'actual_flat', \Input::old( 'actual_flat', $customer->flat ), [ 'class' => 'form-control', 'placeholder' => 'Квартира' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-xs-4">
                        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'lastname', \Input::old( 'lastname', $customer->lastname ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'firstname', \Input::old( 'firstname', $customer->firstname ), [ 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'middlename', \Input::old( 'middlename', $customer->middlename ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-xs-4">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', \Input::old( 'phone', $customer->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone2', \Input::old( 'phone2', $customer->phone2 ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::email( 'email', \Input::old( 'email', $customer->email ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
                    </div>

                </div>

                <div class="form-group hidden-print">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        @if ( \Auth::user()->canOne( [ 'catalog.customers.tickets', 'catalog.customers.calls' ] ) )

            <ul class="nav nav-tabs">
                @if ( \Auth::user()->can( 'catalog.customers.tickets' ) )
                    <li class="active">
                        <a data-toggle="tab" href="#tickets">
                            Заявки
                            <span class="badge" id="tickets-count">{{ $customer->tickets->count() }}</span>
                        </a>
                    </li>
                @endif
                @if ( \Auth::user()->can( 'catalog.customers.calls' ) )
                    <li>
                        <a data-toggle="tab" href="#calls">
                            Звонки
                            <span class="badge" id="calls-count">{{ $customer->calls()->count() }}</span>
                        </a>
                    </li>
                @endif
            </ul>

            <div class="tab-content">
                <div id="tickets" class="tab-pane fade in active">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @if ( ! $customer->tickets->count() )
                                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                            @else
                                <table class="table table-hover table-striped">
                                    <thead>
                                    <tr>
                                        <th>
                                            Номер заявки
                                        </th>
                                        <th>
                                            Дата заявки
                                        </th>
                                        <th>
                                            Адрес проблемы
                                        </th>
                                        <th>
                                            Тип заявки
                                        </th>
                                        <th>
                                            Статус заявки
                                        </th>
                                        <th class="hidden-print">
                                            &nbsp;
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach( $customer->tickets as $ticket )
                                        <tr>
                                            <td>
                                                <a href="{{ route( 'tickets.show', $ticket->id ) }}" target="_blank">
                                                    {{ $ticket->id }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                                            </td>
                                            <td>
                                                {{ $ticket->getAddress() }}
                                                <span class="small text-muted">
                                            ({{ $ticket->getPlace() }})
                                        </span>
                                            </td>
                                            <td>
                                                <div class="bold">{{ $ticket->type->category->name }}</div>
                                                {{ $ticket->type->name }}
                                            </td>
                                            <td>
                                        <span class="text-{{ $ticket->getClass() }}">
                                            {{ $ticket->status_name }}
                                        </span>
                                            </td>
                                            <td class="text-right hidden-print">
                                                <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="btn btn-lg btn-primary tooltips" title="Открыть обращение #{{ $ticket->id }}" target="_blank">
                                                    <i class="fa fa-chevron-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="calls" class="tab-pane fade">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @if ( ! $customer->calls()->count() )
                                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                            @else
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">
                                                Дата звонка
                                            </th>
                                            <th colspan="2" class="text-center">
                                                Оператор
                                            </th>
                                            @if ( \Auth::user()->can( 'calls' ) )
                                                <th rowspan="2">
                                                    Запись
                                                </th>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>
                                                ФИО
                                            </th>
                                            <th>
                                                Номер
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ( $customer->calls() as $call )
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse( $call->calldate )->format( 'd.m.Y H:i' ) }}
                                            </td>
                                            <td>
                                                @if ( $call->queueLog->operator() )
                                                    {{ $call->queueLog->operator()->getShortName() }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $call->queueLog->number() }}
                                            </td>
                                            @if ( \Auth::user()->can( 'calls' ) )
                                                <td>
                                                    @if ( $call->hasMp3() )
                                                        <a href="{{ $call->getMp3() }}" target="_blank">
                                                            {{ $call->getMp3() }}
                                                        </a>
                                                    @else
                                                        <span class="text-danger">
                                                    Запись не найдена
                                                </span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @endif

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

            });

    </script>
@endsection