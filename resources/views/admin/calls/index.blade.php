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
                {!! Form::select( 'operator_id', [ null => ' -- ВСЕ -- ' ] + $operators, $operator_id, [ 'class' => 'form-control select2' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( null, 'Номер', [ 'class' => 'col-md-3 col-xs-2 control-label' ] ) !!}
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'caller', \Input::get( 'caller' ), [ 'class' => 'form-control', 'placeholder' => 'Кто звонит', 'minlength' => 2, 'maxlength' => 10 ] ) !!}
            </div>
            <div class="col-md-3 col-xs-5">
                {!! Form::text( 'answer', \Input::get( 'answer' ), [ 'class' => 'form-control', 'placeholder' => 'Кому звонят', 'minlength' => 2, 'maxlength' => 10 ] ) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'col-md-3 col-xs-2 control-label' ] ) !!}
            <div class="col-md-3 col-xs-5">
                <input class="form-control" name="date_from" type="datetime-local" value="{{ $date_from->format( 'Y-m-d\TH:i' ) }}" id="date_from" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
            </div>
            <div class="col-md-3 col-xs-5">
                <input class="form-control" name="date_to" type="datetime-local" value="{{ $date_to->format( 'Y-m-d\TH:i' ) }}" id="date_to" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
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