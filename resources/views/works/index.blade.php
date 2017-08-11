@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Работа на сетях' ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'works.create' )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'works.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить сообщение
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

            {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr class="info">
                        <th>
                             Номер сообщения
                        </th>
                        <th>
                            Основание
                        </th>
                        <th>
                            Адрес работ
                        </th>
                        <th>
                            Тип работ
                        </th>
                        <th>
                            Исполнитель работ
                        </th>
                        <th>
                            Состав работ
                        </th>
                        <th>
                            &nbsp;Дата и время начала
                        </th>
                        <th>
                            &nbsp;Дата и время окончания (план)
                        </th>
                        <th>
                            &nbsp;Комментарии
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    <tr class="info">
                        <td>
                            {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер обращения' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'reason', \Input::old( 'reason' ), [ 'class' => 'form-control', 'placeholder' => 'Основание' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'type', \Input::old( 'type' ), [ 'class' => 'form-control', 'placeholder' => 'Тип' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'management', \Input::old( 'management' ), [ 'class' => 'form-control', 'placeholder' => 'Исполнитель' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'composition', \Input::old( 'composition' ), [ 'class' => 'form-control', 'placeholder' => 'Состав' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'datetime_begin', \Input::old( 'datetime_begin' ), [ 'class' => 'form-control date-picker', 'placeholder' => 'Начало', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'datetime_end', \Input::old( 'datetime_end' ), [ 'class' => 'form-control date-picker', 'placeholder' => 'Окончание', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                        </td>
                        <td>
                            {!! Form::text( 'text', \Input::old( 'text' ), [ 'class' => 'form-control', 'placeholder' => 'Комментарии' ] ) !!}
                        </td>
                        <td class="text-right">
                            <button type="submit" class="btn btn-primary tooltips" title="Применить фильтр">
                                <i class="fa fa-filter"></i>
                            </button>
                        </td>
                    </tr>
                </thead>
                <tbody>
                @foreach ( $works as $work )
                    @include( 'parts.work', [ 'work' => $work ] )
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