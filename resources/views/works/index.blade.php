@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'works.show', 'works.all' ) )

        @if( \Auth::user()->canOne( 'works.create', 'works.export' ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @can( 'works.create' )
                        <a href="{{ route( 'works.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить сообщение
                        </a>
                    @endcan
                </div>
                <div class="col-xs-6 text-right">
                    @can( 'works.export' )
                        <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        <div class="row hidden-print">
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

                {{ $works->render() }}

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                        {!! Form::hidden( 'show', \Input::get( 'show', null ) ) !!}
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
                                Категория
                            </th>
                            <th>
                                Исполнитель работ
                            </th>
                            <th>
                                Состав работ
                            </th>
                            <th>
                                &nbsp;Дата начала
                            </th>
                            <th colspan="3">
                                &nbsp;Дата окончания (План.|Факт.)
                            </th>
                        </tr>
                        <tr class="info hidden-print">
                            <td width="10%">
                                {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
                            </td>
                            <td width="10%">
                                {!! Form::text( 'reason', \Input::old( 'reason' ), [ 'class' => 'form-control', 'placeholder' => 'Основание' ] ) !!}
                            </td>
                            <td width="15%">
                                {!! Form::select( 'address_id', $address, \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес', 'data-ajax--url' => route( 'addresses.search' ), 'data-placeholder' => 'Адрес работы' ] ) !!}
                            </td>
                            <td width="15%">
                                {!! Form::select( 'category_id', [ null => ' -- все -- ' ] + \App\Models\Work::$categories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
                            </td>
                            <td width="10%">
                                {!! Form::select( 'management_id', [ null => ' -- все -- ' ] + $managements->pluck( 'name', 'id' )->toArray(), \Input::old( 'management_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'ЭО' ] ) !!}
                            </td>
                            <td width="10%">
                                {!! Form::text( 'composition', \Input::old( 'composition' ), [ 'class' => 'form-control', 'placeholder' => 'Состав' ] ) !!}
                            </td>
                            <td colspan="4">
                                <div class="row">
                                    <div class="col-lg-12 text-right">
                                        <span class="text-muted small bold">
                                            Фильтр:
                                        </span>
                                        <a href="{{ route( 'works.index' ) }}" class="btn btn-sm btn-default tooltips" title="Очистить фильтр">
                                            <i class="icon-close"></i>
                                            Очистить
                                        </a>
                                        <button type="submit" class="btn btn-sm btn-primary tooltips bold" title="Применить фильтр">
                                            <i class="icon-check"></i>
                                            Применить
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        {!! Form::close() !!}
                    </thead>
                    @if ( $works->count() )
                        <tbody>
                        @foreach ( $works as $work )
                            @include( 'parts.work', [ 'work' => $work ] )
                        @endforeach
                        </tbody>
                    @endif
                </table>

                {{ $works->render() }}

                @if ( ! $works->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

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