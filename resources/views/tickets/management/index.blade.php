@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row">
        <div class="col-xs-12">
            {!! Form::open( [ 'method' => 'get' ] ) !!}
                <div class="input-group">
                    {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control input-lg', 'placeholder' => 'Быстрый поиск...' ] ) !!}
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-search"></i>
                            Поиск
                        </button>
                    </span>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-xs-12">

            {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>
                             Статус \ Номер обращения \ Оценка
                        </th>
                        <th>
                            Дата и время создания
                        </th>
                        <th>
                            Адрес проблемы \ группа
                        </th>
                        <th>
                            Категория и тип обращения
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    <tr>
                        <td>
                            {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер обращения' ] ) !!}
                        </td>
                        <td>
                            <div class="input-group date-picker input-daterange" data-date-format="dd.mm.yyyy">
                                {!! Form::text( 'period_from', \Input::old( 'period_from' ), [ 'class' => 'form-control', 'placeholder' => 'Период ОТ' ] ) !!}
                                <span class="input-group-addon"> - </span>
                                {!! Form::text( 'period_to', \Input::old( 'period_to' ), [ 'class' => 'form-control', 'placeholder' => 'Период ДО' ] ) !!}
                            </div>
                        </td>
                        <td>
                            {!! Form::text( 'address_id', \Input::old( 'address_id' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес проблемы' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'type_id', \Input::old( 'type_id' ), [ 'class' => 'form-control', 'placeholder' => 'Тип обращения' ] ) !!}
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                </thead>
                <tbody>
                @foreach ( $ticketManagements as $ticketManagement )
                    @include( 'parts.ticket_management', [ 'ticketManagement' => $ticketManagement, 'ticket' => $ticketManagement->ticket ] )
                @endforeach
                </tbody>
            </table>
            {!! Form::close() !!}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <style>
        .alert {
            margin-bottom: 0;
        }
        .mt-element-ribbon {

            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -18px;
            top: -18px;
        }
        .color-inherit {
            color: inherit;
        }
    </style>
@endsection

@section( 'js' )
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
                $('.date-picker').datepicker({
                    rtl: App.isRTL(),
                    orientation: "left",
                    autoclose: true
                });
            });
    </script>
@endsection