@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.calls.show' ) )

        {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal submit-loading hidden-print' ] ) !!}
        <div class="form-group">
            {!! Form::label( 'operator', 'Оператор', [ 'class' => 'control-label col-md-3 col-xs-2' ] ) !!}
            <div class="col-md-6 col-xs-10">
                {!! Form::select( 'operator_id', [ null => ' -- ВСЕ -- ' ] + $availableOperators, $operator_id, [ 'class' => 'form-control select2' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( null, 'Номер', [ 'class' => 'col-md-3 col-xs-2 control-label' ] ) !!}
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'caller', \Input::get( 'caller' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Кто звонит' ] ) !!}
            </div>
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'answer', \Input::get( 'answer' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Кому звонят' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'col-md-3 col-xs-2 control-label' ] ) !!}
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'date_from', $date_from->format( 'd.m.Y H:i' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => 'От' ] ) !!}
            </div>
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'date_to', $date_to->format( 'd.m.Y H:i' ), [ 'class' => 'form-control datetimepicker', 'placeholder' => 'До' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'status', 'Статус', [ 'class' => 'col-md-3 col-xs-2 control-label' ] ) !!}
            <div class="col-md-3 col-xs-5">
                {!! Form::select( 'status', [ null => ' -- выберите из списка -- ' ] + App\Models\Asterisk\Cdr::$statuses, \Input::get( 'status' ), [ 'class' => 'form-control select2' ] ) !!}
            </div>
            <div class="col-md-3 col-xs-5">
                {!! Form::select( 'context', [ null => 'ВСЕ' ] + $providerContexts->toArray(), \Input::get( 'context' ), [ 'class' => 'form-control select2' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-offset-3 col-md-3">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-search"></i>
                    Поиск
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route( 'calls.index' ) }}" class="btn btn-danger btn-block">
                    <i class="fa fa-close"></i>
                    Сбросить
                </a>
            </div>
        </div>
        {!! Form::close() !!}


        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-md-8">
                        {{ $calls->render() }}
                    </div>
                    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $calls->total() }}</b>
                        </span>
                    </div>
                </div>

                @if ( $calls->count() )

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    Дата звонка
                                </th>
                                <th>
                                    Кто
                                </th>
                                <th>
                                    &nbsp;
                                </th>
                                <th>
                                    Кому
                                </th>
                                <th>
                                    Статус
                                </th>
                                <th class="text-center">
                                    Длительность
                                </th>
                                <th>
                                    Запись
                                </th>
                                <th class="text-right">
                                    Заявка
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ( $calls as $call )
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse( $call->calldate )->format( 'd.m.Y H:i' ) }}
                                </td>
                                <td>
                                    {!! $call->getCaller() !!}
                                </td>
                                <td>
                                    @if ( $call->dcontext == 'incoming' )
                                        <i class="fa fa-chevron-circle-down text-success tooltips" title="Входящий"></i>
                                    @else
                                        <i class="fa fa-chevron-circle-up text-danger tooltips" title="Исходящий"></i>
                                    @endif
                                </td>
                                <td>
                                    {!! $call->getAnswer() !!}
                                </td>
                                <td>
                                    {{ $call->getStatus() }}
                                </td>
                                @if ( $call->billsec > 0 )
                                    <td class="text-center">
                                        {{ date( 'H:i:s', mktime( 0, 0, $call->billsec ) ) }}
                                    </td>
                                    <td>
                                        @if ( $call->hasMp3() )
                                            <a href="{{ $call->getMp3() }}" target="_blank" class="tooltips" title="Прослушать звонок">
                                                {{ $call->getMp3() }}
                                            </a>
                                        @else
                                            <span class="text-danger">
                                                Запись не найдена
                                            </span>
                                        @endif
                                    </td>
                                @else
                                    <td colspan="2">
                                        &nbsp;
                                    </td>
                                @endif
                                <td class="text-right">
                                    @if ( $call->getContext() == 'incoming' && $call->ticket )
                                        <a href="{{ route( 'tickets.show', $call->ticket->id ) }}" class="tooltips" title="Открыть заявку #{{ $call->ticket->id }}">
                                            #{{ $call->ticket->id }}
                                        </a>
                                    @elseif ( $call->getContext() == 'outgoing' && $call->ticketCall && $call->ticketCall->ticket )
                                        <a href="{{ route( 'tickets.show', $call->ticketCall->ticket->id ) }}" class="tooltips" title="Открыть заявку #{{ $call->ticketCall->ticket->id }}">
                                            #{{ $call->ticketCall->ticket->id }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

                {{ $calls->render() }}

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy',
                });

                $( '.datetimepicker' ).datetimepicker({
                    isRTL: App.isRTL(),
                    format: "dd.mm.yyyy hh:ii",
                    autoclose: true,
                    fontAwesome: true,
                    todayBtn: true
                });

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            });

    </script>

@endsection