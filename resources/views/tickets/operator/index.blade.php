@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'tickets.create' )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить обращение
                </a>
            </div>
        </div>
    @endcan

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

            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr class="info">
                        <th colspan="2">
                             Статус \ Номер обращения \ Оценка
                        </th>
                        <th width="250">
                            Дата и время создания
                        </th>
                        <th>
                            Оператор
                        </th>
                        <th>
                            ЭО
                        </th>
                        <th>
                            Категория и тип обращения
                        </th>
                        <th>
                            Адрес проблемы \ группа
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    <tr class="info">
                        <td colspan="2">
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
                            {!! Form::text( 'operator', \Input::old( 'operator' ), [ 'class' => 'form-control', 'placeholder' => 'Оператор' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'management_id', \Input::old( 'management_id' ), [ 'class' => 'form-control', 'placeholder' => 'ЭО' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'type_id', \Input::old( 'type_id' ), [ 'class' => 'form-control', 'placeholder' => 'Тип обращения' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'address_id', \Input::old( 'address_id' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес проблемы' ] ) !!}
                        </td>
                        <td class="text-right">
                            <button type="submit" class="btn btn-primary tooltips" title="Применить фильтр">
                                <i class="fa fa-filter"></i>
                            </button>
                        </td>
                    </tr>
                </thead>
                {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
                <tbody>
                @foreach ( $tickets as $ticket )
                    @include( 'parts.ticket', [ 'ticket' => $ticket ] )
                    @if ( $ticket->childs->count() )
                        @foreach ( $ticket->childs as $child )
                            @include( 'parts.ticket', [ 'ticket' => $child ] )
                        @endforeach
                    @endif
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">
                            Действия с выделенными
                        </th>
                        <td colspan="5">
                            @can ( 'tickets.group' )
                                <button type="submit" name="action" value="group" class="btn btn-default">
                                    Группировать
                                </button>
                                <button type="submit" name="action" value="ungroup" class="btn btn-default">
                                    Разгруппировать
                                </button>
                            @endcan
                            @can ( 'tickets.delete' )
                                <button type="submit" name="action" value="delete" class="btn btn-danger hidden">
                                    Удалить
                                </button>
                            @endcan
                        </td>
                    </tr>
                </tfoot>
                {!! Form::close() !!}
            </table>

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
        .border-left {
            border-left: 2px solid #337ab7 !important;
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