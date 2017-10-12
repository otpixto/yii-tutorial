@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'tickets.create', 'tickets.export' )
        <div class="row margin-bottom-15 hidden-print">
            <div class="col-xs-6">
                @can( 'tickets.create' )
                    <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-plus"></i>
                        Добавить заявку
                    </a>
                @endcan
            </div>
            <div class="col-xs-6 text-right">
                @can( 'tickets.export' )
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

            <div class="row">
                <div class="col-xs-8">
                    {{ $ticketManagements->render() }}
                </div>
                <div class="col-xs-4 text-right margin-top-10 margin-bottom-10">
                    <span class="label label-info">
                        Найдено: <b>{{ $ticketManagements->total() }}</b>
                    </span>
                </div>
            </div>

            <table class="table table-striped table-bordered table-hover">
                {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                <thead>
                    <tr class="info">
                        <th width="250">
                             Статус \ Номер заявки
                        </th>
                        <th width="220">
                            Дата и время создания
                        </th>
                        @if ( $field_operator )
                            <th width="150">
                                Оператор
                            </th>
                        @endif
                        @if ( $field_management )
                            <th width="200">
                                ЭО
                            </th>
                        @endif
                        <th width="200">
                            Категория и тип заявки
                        </th>
                        <th>
                            Адрес проблемы
                        </th>
                        <th class="hidden-print">
                            &nbsp;
                        </th>
                    </tr>
                    <tr class="info hidden-print">
                        <td>
                            <div class="row">
                                <div class="col-lg-7">
                                    {!! Form::select( 'status_code', [ null => ' -- все -- ' ] + \Auth::user()->getAvailableStatuses( true ), \Input::old( 'status_code' ), [ 'class' => 'form-control select2', 'placeholder' => 'Статус' ] ) !!}
                                </div>
                                <div class="col-lg-5">
                                    {!! Form::text( 'id', \Input::old( 'id' ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="date-picker input-daterange" data-date-format="dd.mm.yyyy">
								<div class="row">
									<div class="col-lg-6">
										{!! Form::text( 'period_from', \Input::old( 'period_from' ), [ 'class' => 'form-control', 'placeholder' => 'ОТ' ] ) !!}
									</div>
									<div class="col-lg-6">
										{!! Form::text( 'period_to', \Input::old( 'period_to' ), [ 'class' => 'form-control', 'placeholder' => 'ДО' ] ) !!}
									</div>
								</div>
							</div>
                        </td>
                        @if ( $field_operator )
                            <td>
                                {!! Form::select( 'operator_id', [ null => ' -- все -- ' ] + $operators->pluck( 'lastname', 'id' )->toArray(), \Input::old( 'operator_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Оператор' ] ) !!}
                            </td>
                        @endif
                        @if ( $field_management )
                            <td>
                                {!! Form::select( 'management_id', [ null => ' -- все -- ' ] + $managements->pluck( 'name', 'id' )->toArray(), \Input::old( 'management_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'ЭО' ] ) !!}
                            </td>
                        @endif
                        <td>
                            {!! Form::select( 'type_id', [ null => ' -- все -- ' ] + $types->pluck( 'name', 'id' )->toArray(), \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип заявки' ] ) !!}
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-lg-7">
                                    {!! Form::select( 'address_id', [ null => ' -- все -- ' ] + ( $address ? $address->pluck( 'name', 'id' )->toArray() : [] ), \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проблемы', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес работы', 'data-allow-clear' => true ] ) !!}
                                </div>
                                <div class="col-lg-5">
                                    {!! Form::text( 'flat', \Input::old( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Кв.' ] ) !!}
                                </div>
                            </div>
                        </td>
                        <td class="text-right hidden-print">
                            <button type="submit" class="btn btn-primary tooltips" title="Применить фильтр">
                                <i class="fa fa-filter"></i>
                            </button>
                        </td>
                    </tr>
                </thead>
                {!! Form::close() !!}
                {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}
                <tbody>
                @if ( $ticketManagements->count() )
                    @foreach ( $ticketManagements as $ticketManagement )
                        @include( 'parts.ticket', [ 'ticketManagement' => $ticketManagement ] )
                    @endforeach
                    </tbody>
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.group' ) || \Auth::user()->can( 'tickets.delete' ) )
                        <tfoot class="hidden-print">
                            <tr>
                                <th colspan="{{ 2 + ( $field_operator ? 1 : 0 ) }}" class="text-right">
                                    Действия с выделенными
                                </th>
                                <td colspan="{{ 4 + ( $field_management ? 1 : 0 ) }}">
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
                    @endif
                    {!! Form::close() !!}
                @else
                    </tbody>
                @endif
            </table>

            {{ $ticketManagements->render() }}

            @if ( ! $ticketManagements->count() )
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/clockface/css/clockface.css" rel="stylesheet" type="text/css" />
    <style>
        .alert, .mt-element-ribbon, .note {
            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -19px;
            top: -19px;
        }
        .color-inherit {
            color: inherit;
        }
        .border-left {
            border-left: 2px solid #b71a00 !important;
        }
        .opacity {
            opacity: 0.5;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
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

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    allowClear: true,
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