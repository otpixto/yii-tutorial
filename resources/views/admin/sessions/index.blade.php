@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i>
                Активные сессии
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""> </a>
                <a href="javascript:;" class="remove" data-original-title="" title=""> </a>
            </div>
        </div>
        <div class="portlet-body">
            @if ( $activeSessions->count() )
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>
                                ФИО
                            </th>
                            <th class="text-center">
                                Номер
                            </th>
                            <th>
                                &nbsp;
                            </th>
                        </tr>
                    </thead>
                    @foreach ( $activeSessions as $activeSession )
                        <tr>
                            <td>
                                {{ $activeSession->user->getName() }}
                            </td>
                            <td class="text-center">
                                {{ $activeSession->number }}
                            </td>
                            <td class="text-right">
                                @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.close' ) )
                                    {!! Form::model( $activeSession, [ 'method' => 'delete', 'route' => [ 'sessions.destroy', $activeSession->id ], 'data-confirm' => 'Вы уверены, что хотите завершить сессию?', 'class' => 'submit-loading' ] ) !!}
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fa fa-close"></i>
                                        Завершить сессию
                                    </button>
                                    {!! Form::close() !!}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                @include( 'error', [ 'error' => 'Активных сессий нет' ] )
            @endif
        </div>
    </div>

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.show' ) )

        @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.create' ) )
            <div class="row margin-bottom-15">
                <div class="col-xs-12">
                    <a href="{{ route( 'sessions.create' ) }}" class="btn btn-success">
                        <i class="fa fa-plus"></i>
                        Добавить в очередь
                    </a>
                </div>
            </div>
        @endif

        {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal submit-loading hidden-print' ] ) !!}
        <div class="form-group">
            {!! Form::label( 'operator', 'Оператор', [ 'class' => 'col-md-3 col-xs-4 control-label' ] ) !!}
            <div class="col-md-3 col-xs-4">
                {!! Form::select( 'operator', [ 0 => ' -- все --' ] + $operators, \Input::get( 'operator' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Оператор' ] ) !!}
            </div>
            <div class="col-md-3 col-xs-4">
                {!! Form::text( 'number', \Input::get( 'number' ), [ 'class' => 'form-control', 'placeholder' => 'Номер', 'maxlength' => '10' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'col-md-3 col-xs-4 control-label' ] ) !!}
            <div class="col-md-3 col-xs-4">
                {!! Form::text( 'date_from', $date_from->format( 'd.m.Y' ), [ 'class' => 'form-control date-picker', 'placeholder' => 'От' ] ) !!}
            </div>
            <div class="col-md-3 col-xs-4">
                {!! Form::text( 'date_to', $date_to->format( 'd.m.Y' ), [ 'class' => 'form-control date-picker', 'placeholder' => 'До' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-offset-4 col-md-offset-3 col-md-2 col-xs-4">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-search"></i>
                    Поиск
                </button>
            </div>
        </div>
        {!! Form::close() !!}


        <div class="row margin-top-15">
            <div class="col-xs-12">

                {{ $sessions->render() }}

                @if ( $sessions->count() )

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">
                                    Оператор
                                </th>
                                <th rowspan="2">
                                    Начало сессии
                                </th>
                                <th rowspan="2">
                                    Окончание сессии
                                </th>
                                <th rowspan="2">
                                    Длительность
                                </th>
                                <th rowspan="2">
                                    &nbsp;
                                </th>
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
                        @foreach ( $sessions as $session )
                            <tr @if ( ! $session->closed_at ) class="success" @endif>
                                <td>
                                    {!! $session->user->getFullName() !!}
                                </td>
                                <td>
                                    {{ $session->number }}
                                </td>
                                <td>
                                    {{ $session->created_at->format( 'd.m.Y H:i' ) }}
                                </td>
                                @if ( $session->closed_at )
                                    <td>
                                        {{ $session->closed_at->format( 'd.m.Y H:i' ) }}
                                    </td>
                                    <td>
                                        @if ( $session->closed_at->diffInDays( $session->created_at ) >= 1 )
                                            {{ $session->closed_at->diffInDays( $session->created_at ) }} д.
                                        @endif
                                        {{ date( 'H:i:s', mktime( 0, 0, $session->closed_at->diffInSeconds( $session->created_at ) ) ) }}
                                    </td>
                                @else
                                    <td colspan="2">
                                        @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.close' ) )
                                            {!! Form::model( $session, [ 'method' => 'delete', 'route' => [ 'sessions.destroy', $session->id ], 'data-confirm' => 'Вы уверены, что хотите завершить сессию?', 'class' => 'submit-loading' ] ) !!}
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa fa-close"></i>
                                                Завершить сессию
                                            </button>
                                            {!! Form::close() !!}
                                        @endif
                                    </td>
                                @endif
                                <td class="text-right">
                                    <a href="{{ route( 'sessions.show', $session->id ) }}" class="btn btn-lg btn-primary tooltips" title="Просмотр сессии">
                                        <i class="fa fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

                {{ $sessions->render() }}

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.select2' ).select2();

                $( '.date-picker' ).datepicker({
                    format: 'dd.mm.yyyy'
                });

            });

    </script>

@endsection