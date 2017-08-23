@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр обращений', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-top-15">
        <div class="col-xs-12">

            @if ( $tickets->count() )

                {{ $tickets->render() }}

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="info">
                            <th>
                                Номер обращения
                            </th>
                            <th>
                                ФИО Заявителя
                            </th>
                            <th>
                                Телефон(ы) Заявителя
                            </th>
                            <th>
                                ЭО
                            </th>
                            <th>
                                Категория и тип обращения
                            </th>
                            <th>
                                Адрес проблемы
                            </th>
                            <th class="hidden-print">
                                &nbsp;
                            </th>
                        </tr>
                    </thead>
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
                                    ({{ $ticket->place }})
                                </span>
                            </td>
                            <td class="text-right hidden-print">
                                <div class="text-nowrap">
                                    <a href="javascript:;" class="btn btn-lg btn-success tooltips" title="Закрыть с подтверждением" data-action="close-rate" data-id="{{ $ticket->id }}">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    <a href="javascript:;" class="btn btn-lg btn-warning tooltips" title="Закрыть без подтверждением" data-action="close" data-id="{{ $ticket->id }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    <a href="javascript:;" class="btn btn-lg btn-danger tooltips" title="Передать ЭО повторно" data-action="repeat" data-id="{{ $ticket->id }}">
                                        <i class="fa fa-repeat"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @if ( $ticket->comments->count() )
                            <tr>
                                <td colspan="6">
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

            })

            .on( 'click', '[data-action="close-rate"]', function ( e )
            {

                e.preventDefault();

                var id = $( this ).attr( 'data-id' );

                var dialog = bootbox.dialog({
                    title: 'Оцените работу ЭО',
                    message: '<p><i class="fa fa-spin fa-spinner"></i> Загрузка... </p>'
                });

                dialog.init( function ()
                {
                    $.get( '{{ route( 'tickets.rate' ) }}', {
                        id: id
                    }, function ( response )
                    {
                        dialog.find( '.bootbox-body' ).html( response );
                    });
                });

            })

            .on( 'click', '[data-action="close"]', function ( e )
            {

                e.preventDefault();

                var id = $( this ).attr( 'data-id' );

                bootbox.confirm({
                    message: 'Закрыть данное обращение без подтверждения?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            $.post( '{{ route( 'tickets.close' ) }}', {
                                id: id
                            }, function ( response )
                            {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-action="repeat"]', function ( e )
            {

                e.preventDefault();

                var id = $( this ).attr( 'data-id' );

                bootbox.confirm({
                    message: 'Передать повторно обращение ЭО?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            $.post( '{{ route( 'tickets.repeat' ) }}', {
                                id: id
                            }, function ( response )
                            {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-rate]', function ( e )
            {

                e.preventDefault();

                var rate = $( this ).attr( 'data-rate' );
                var form = $( '#rate-form' );

                form.find( '[name="rate"]' ).val( rate );

                if ( rate < 4 )
                {
                    bootbox.prompt({
                        title: 'Введите комментарий к оценке',
                        inputType: 'textarea',
                        callback: function (result) {
                            if ( !result ) {
                                alert('Действие отменено!');
                            }
                            else {
                                form.find('[name="comment"]').val(result);
                                form.submit();
                            }
                        }
                    });
                }
                else
                {
                    form.submit();
                }

            });

    </script>
@endsection