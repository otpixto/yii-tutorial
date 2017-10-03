@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-top-15">
        <div class="col-xs-12">

            @if ( $tickets->count() )

                {{ $tickets->render() }}

                <table class="table table-striped table-bordered table-hover">
                    {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                    <thead>
                        <tr class="info">
                            <th>
                                Номер заявки
                            </th>
                            <th width="15%">
                                ФИО Заявителя \ Телефон(ы) Заявителя
                            </th>
                            <th width="15%">
                                Телефон(ы) Заявителя
                            </th>
                            <th width="15%">
                                ЭО
                            </th>
                            <th width="15%">
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
                                {!! Form::text( 'id', \Input::get( 'id' ), [ 'class' => 'form-control' ] ) !!}
                            </td>
                            <td>
                                {!! Form::text( 'name', \Input::get( 'name' ), [ 'class' => 'form-control' ] ) !!}
                            </td>
                            <td>
                                {!! Form::text( 'phone', \Input::get( 'phone' ), [ 'class' => 'form-control' ] ) !!}
                            </td>
                            <td>
                                {!! Form::select( 'management_id', [ null => ' -- все -- ' ] + $managements->pluck( 'name', 'id' )->toArray(), \Input::old( 'management_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'ЭО' ] ) !!}
                            </td>
                            <td>
                                {!! Form::select( 'type_id', [ null => ' -- все -- ' ] + $types->pluck( 'name', 'id' )->toArray(), \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип заявки' ] ) !!}
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-lg-7">
                                        {!! Form::select( 'address_id', $address ? $address->pluck( 'name', 'id' )->toArray() : [], \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проблемы', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес работы', 'data-allow-clear' => true ] ) !!}
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
                    <tbody>
                    @foreach ( $tickets as $ticket )
                        <tr>
                            <td>
                                {{ $ticket->id }}
                            </td>
                            <td>
                                {{ $ticket->getName() }}
                            </td>
                            <td>
                                {{ $ticket->getPhones() }}
                            </td>
                            <td>
                                @foreach ( $ticket->managements as $ticketManagement )
                                    <div>
                                        {{ $ticketManagement->management->name }}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <div class="bold">
                                    {{ $ticket->type->category->name }}
                                </div>
                                <div class="small">
                                    {{ $ticket->type->name }}
                                </div>
                            </td>
                            <td>
                                {{ $ticket->getAddress() }}
                                <span class="small text-muted">
                                    ({{ $ticket->getPlace() }})
                                </span>
                            </td>
                            <td class="text-right hidden-print">
                                <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="btn btn-lg btn-primary tooltips" title="Открыть заявку #{{ $ticket->id }}">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                        @if ( $ticket->comments->count() )
                            <tr>
                                <td colspan="7">
                                    <div class="note note-info">
                                        @include( 'parts.comments', [ 'ticket' => $ticket, 'comments' => $ticket->comments ] )
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>

                {{ $tickets->render() }}

            @else
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
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
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