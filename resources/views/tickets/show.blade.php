@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div id="ticket-show">

        @include( 'tickets.parts.info' )

    </div>

	{!! Form::hidden( 'ticket_id', $ticket->id, [ 'id' => 'ticket-id' ] ) !!}
    {!! Form::hidden( 'management_id', isset( $ticketManagement ) ? $ticketManagement->management_id : null, [ 'id' => 'management-id' ] ) !!}

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />
    <style>
        dl, .alert {
            margin: 0px;
        }
        .d-inline {
            display: inline;
        }
        .status .status-name {
            font-size: 25px;
            font-weight: bold;
        }
        .status .progress {
            height: 10px;
            margin-bottom: 0;
        }
        #ticket-show .note {
            padding: 10px;
            margin: 3px 0;
            border-width: 2px;
        }
        @media print
        {
            #ticket-services .row {
                border-top: 1px solid #e9e9e9;
            }
            #ticket-services .form-control {
                padding: 0;
                margin: 0;
                border: none;
            }
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-repeater/jquery.repeater.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

    <script type="text/javascript">

        var progressTimer = null;

        function calcTotals ()
        {
            var total = 0;
            $( '#ticket-services [data-repeater-item]' ).each( function ()
            {
                var amount = ( Number( $( this ).find( '.amount' ).val().replace( ',', '.' ) ) || 0 ).toFixed( 2 );
                var quantity = ( Number( $( this ).find( '.quantity' ).val().replace( ',', '.' ) ) || 1 ).toFixed( 2 );
                $( this ).find( '.amount' ).val( amount || '' );
                $( this ).find( '.quantity' ).val( quantity );
                total += amount * quantity;
            });
            $( '#ticket-services-total' ).text( total.toFixed( 2 ) );
        };

        @if ( isset( $ticketManagement ) )

            function setManagements ()
            {
                $.get( '{{ route( 'tickets.managements.select', $ticketManagement->id ) }}', function ( response )
                {
                    Modal.createSimple( 'Назначить УО', response, 'managements' );
                });
            };

            function setExecutor ()
            {
                $.get( '{{ route( 'tickets.executor.select', $ticketManagement->id ) }}', function ( response )
                {
                    Modal.createSimple( 'Назначить исполнителя', response, 'executor' );
                });
            };

        @endif

        function getProgressData ()
        {
            $.get( '{{ route( 'tickets.progress', $ticket->id ) }}', function ( response )
            {
                if ( ! response.percent )
                {
                    $( '#progress .progress-bar' ).remove();
                    window.clearInterval( progressTimer );
                    progressTimer = null;
                }
                else
                {
                    $( '#status-title' ).text( response.title );
                    $( '#progress .progress-bar' )
                        .attr( 'class', response.class )
                        .css( 'width', response.percent + '%' );
                }
                if ( response.percent >= 100 )
                {
                    window.clearInterval( progressTimer );
                    progressTimer = null;
                }
            });
        };

        $( document )

            .ready( function ()
            {

                if ( $( '#progress .progress-bar' ).length )
                {
                    var progressTimer = window.setInterval( getProgressData, 60000 );
                }

            })

            .on( 'itemAdded', '#tags', function ( e )
            {
                var tag = e.item;
                if ( ! tag ) return;
                $.post( '{{ route( 'tickets.tags.add', $ticket->id ) }}', {
                    tag: tag
                });
                var tags = $( this ).tagsinput( 'items' );
                $( '#tags_show' ).empty();
                $.each( tags, function ( i, tag )
                {
                    $( '#tags_show' ).append(
                        $( '<a>' )
                            .text( '#' + tag )
                            .attr( 'href', '{{ route( 'tickets.index' ) }}?tags=' + tag )
                            .attr( 'class', 'label label-info margin-right-10' )
                    );
                });
            })

            .on( 'itemRemoved', '#tags', function ( e )
            {
                var tag = e.item;
                if ( ! tag ) return;
                $.post( '{{ route( 'tickets.tags.del', $ticket->id ) }}', {
                    tag: tag
                });
                var tags = $( this ).tagsinput( 'items' );
                $( '#tags_show' ).empty();
                $.each( tags, function ( i, tag )
                {
                    $( '#tags_show' ).append(
                        $( '<a>' )
                            .text( '#' + tag )
                            .attr( 'href', '{{ route( 'tickets.index' ) }}?tags=' + tag )
                            .attr( 'class', 'label label-info margin-right-10' )
                    );
                });
            })

            .on ( 'click', '.nav-tabs a', function ( e )
            {
                $( this ).tab( 'show' );
                switch ( $( this ).attr( 'href' ) )
                {

                    case '#customer_tickets':
                        if ( $( '#customer_tickets' ).text() == '' )
                        {
                            $( '#customer_tickets' ).loading();
                            $.get( '{{ route( 'tickets.customers', $ticket->id ) }}', function ( response )
                            {
                                $( '#customer_tickets' ).html( response );
                            });
                        }
                        break;

                    case '#address_tickets':
                        if ( $( '#address_tickets' ).text() == '' )
                        {
                            $( '#address_tickets' ).loading();
                            $.get( '{{ route( 'tickets.address', $ticket->id ) }}', function ( response )
                            {
                                $( '#address_tickets' ).html( response );
                            });
                        }
                        break;

                    case '#neighbors_tickets':
                        if ( $( '#neighbors_tickets' ).text() == '' )
                        {
                            $( '#neighbors_tickets' ).loading();
                            $.get( '{{ route( 'tickets.neighbors', $ticket->id ) }}', function ( response )
                            {
                                $( '#neighbors_tickets' ).html( response );
                            });
                        }
                        break;

                    case '#works':
                        $( '#works' ).loading();
                        $.get( '{{ route( 'tickets.works', $ticket->id ) }}', function ( response )
                        {
                            $( '#works' ).html( response );
                        });
                        break;

                    case '#location':

                        @if ( $ticket->building && $ticket->building->lon != -1 && $ticket->building->lat != -1 )
                            if ( $( '#location-map' ).attr( 'data-init' ) != '1' )
                            {
                                $( '#location-map' ).attr( 'data-init', '1' );
                                ymaps.ready( function ()
                                {
                                    var myMap = new ymaps.Map( 'location-map', {
                                        center: [{{ $ticket->building->lat }}, {{ $ticket->building->lon }}],
                                        zoom: 17,
                                        controls: [ 'zoomControl' ]
                                    }, {
                                        searchControlProvider: 'yandex#search'
                                    });
                                    myMap.geoObjects
                                        .add(
                                            new ymaps.Placemark( [{{ $ticket->building->lat }}, {{ $ticket->building->lon }}], {
                                                balloonContent: '{{ $ticket->building->name }}'
                                            })
                                        );
                                    myMap.behaviors.disable( 'scrollZoom' );
                                });
                            }
                        @endif

                        break;

                    case '#history':
                        $( '#history' ).loading();
                        $.get( '{{ route( 'tickets.history', $ticketManagement ? $ticketManagement->getTicketNumber() : $ticket->id ) }}', function ( response )
                        {
                            $( '#history' ).html( response );
                        });
                        break;

                @if ( $ticketManagement )

                    case '#services':
                        $( '#services' ).loading();
                        $.get( '{{ route( 'tickets.services', $ticketManagement->id ) }}', function ( response )
                        {
                            $( '#services' ).html( response );
                            $( '#ticket-services' ).repeater({
                                show: function ()
                                {
                                    $( this ).slideDown();
                                },
                                hide: function ( deleteElement )
                                {
                                    if ( confirm( 'Уверены, что хотите удалить строку?' ) )
                                    {
                                        $( this ).slideUp( deleteElement, function ()
                                        {
                                            $( this ).remove();
                                            calcTotals();
                                        });
                                    }
                                },
                                ready: function ( setIndexes )
                                {

                                },
                                defaultValues: {
                                    quantity: '1.00',
                                    unit: 'шт'
                                }
                            });
                        });
                        break;
                @endif

                }
            })

            .on( 'change', '.calc-totals', calcTotals )

            .on( 'click', '[data-rate]', function ( e )
            {

                e.preventDefault();

                var rate = $( this ).attr( 'data-rate' );
                var form = $( this ).closest( '#rate-form' );

				bootbox.confirm({
					message: 'Вы уверены, что хотите поставить оценку ' + rate + '?',
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
					callback: function ( res )
					{

						if ( ! res ) return;

						form.find( '[name="rate"]' ).val( rate );

						if ( rate < 4 || rate == '5+' )
						{
							bootbox.prompt({
								title: 'Введите комментарий к оценке',
								inputType: 'textarea',
								callback: function (result) {
									if ( !result ) {
										alert('Действие отменено!');
									}
									else {
                                        form
                                            .append(
                                                $( '<input type="hidden" name="comment" />' ).val( result )
                                            );
                                        form.submit();
									}
								}
							});
						}
						else
						{
							form.submit();
						}

					}
				});

            })

			.on( 'click', '[data-edit]', function ( e )
			{
				e.preventDefault();
				var param = $( this ).attr( 'data-edit' );
				var id = $( this ).attr( 'data-id' );
				switch ( param )
                {
                    case 'executor':
                        setExecutor();
                        break;
                    case 'managements':
                        setManagements();
                        break;
                    default:
                        $.get( '{{ route( 'tickets.edit', $ticket ) }}', {
                            param: param,
                            id: id,
                        }, function ( response )
                        {
                            Modal.createSimple( 'Редактировать заявку', response, 'edit-' + param );
                            if ( param == 'schedule' )
                            {
                                $( '.datepicker' ).datepicker({
                                    rtl: App.isRTL(),
                                    orientation: "left",
                                    autoclose: true,
                                    format: 'dd.mm.yyyy'
                                });
                            }
                        });
                        break;
                }

			})

			@if ( isset( $ticketManagement ) )

                .on( 'submit', '#executor-form', function ( e )
                {

                    var form = $( this ).closest( 'form' );
                    var confirmed = form.attr( 'data-confirmed' );

                    if ( confirmed == 1 ) return true;

                    e.preventDefault();

                    var data = $( this ).serialize();

                    function sendData ()
                    {
                        if ( ! connected )
                        {
                            form.submit();
                            return true;
                        }
                        $.post( form.attr( 'action' ), data, function ()
                        {
                            Modal.hide( 'executor' );
                            swal({
                                title: 'Успешно',
                                //text: response.success,
                                type: 'success',
                                allowOutsideClick: true
                            });
                        });
                    };

                    $.post( '{{ route( 'tickets.executor.check' ) }}', data, function ( response )
                    {
                        if ( response == '0' )
                        {
                            form.attr( 'data-confirmed', 1 );
                            sendData();
                        }
                        else if ( response.finded )
                        {
                            bootbox.confirm({
                                message: 'Исполнителю уже назначено время с ' + response.finded.scheduled_begin + ' по ' + response.finded.scheduled_end + ' для заявки #' + response.finded.number + '. Все равно назначить?',
                                buttons: {
                                    confirm: {
                                        label: 'Да',
                                        className: 'btn-success'
                                    },
                                    cancel: {
                                        label: 'Нет',
                                        className: 'btn-danger'
                                    }
                                },
                                callback: function ( result )
                                {
                                    if ( result )
                                    {
                                        form.attr( 'data-confirmed', 1 );
                                        sendData();
                                    }
                                }
                            });
                        }
                    })
                    .fail( function ( err )
                    {
                        $.each( err.responseJSON, function ( field, error )
                        {
                            swal({
                                title: 'Ошибка',
                                text: error,
                                type: 'error',
                                allowOutsideClick: true
                            });
                            return;
                        });
                    });

                })

				.on( 'confirmed', '[data-status="assigned"]', function ( e, pe )
				{

					e.preventDefault();
					pe.preventDefault();

					if ( $( this ).hasClass( 'submit-loading' ) )
					{
						$( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
					}

                    setExecutor();

				})

                @if ( $ticketManagement->canRate() )
                    .on( 'confirmed', '[data-status="closed_with_confirm"]', function ( e, pe )
                    {

                        if ( $( '#rate' ).length ) return;

                        e.preventDefault();
                        pe.preventDefault();

                        if ( $( this ).hasClass( 'submit-loading' ) )
                        {
                            $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                        }

                        var dialog = bootbox.dialog({
                            title: 'Оцените работу УО',
                            message: '<p><i class="fa fa-spin fa-spinner"></i> Загрузка... </p>'
                        });

                        dialog.init( function ()
                        {
                            $.get( '{{ route( 'tickets.rate', $ticketManagement->id ) }}', function ( response )
                            {
                                dialog.find( '.bootbox-body' ).html( response );
                            });
                        });

                    })
                @endif
				
			@endif

            .on( 'confirmed', '[data-status="rejected"]', function ( e, pe )
            {

                e.preventDefault();
                pe.preventDefault();

                var form = $( pe.target );

                if ( $( this ).hasClass( 'submit-loading' ) )
                {
                    $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                }

                var id = $( this ).attr( 'data-id' );

                bootbox.prompt({
                    title: 'Укажите причину отклонения заявки',
                    inputType: 'select',
                    inputOptions: [
                        {
                            text: 'Выберите из списка',
                            value: '',
                        },
                        {
                            text: 'Отклонено. Ответ по проблеме предоставлялся ранее',
                            value: 4929,
                        },
                        {
                            text: 'Отклонено. Объект не находится в обслуживании организации',
                            value: 5370,
                        },
                        {
                            text: 'Отклонено. Вопрос не в компетенции организации',
                            value: 5517,
                        }
                    ],
                    callback: function ( result )
                    {
                        if ( result === null )
                        {
                            alert( 'Действие отменено!' );
                            return true;
                        }
                        if ( result == '' )
                        {
                            alert( 'Причина обязательна!' );
                            return false;
                        }
                        form
                            .removeAttr( 'data-confirm' )
                            .append(
                                $( '<input type="hidden" name="reject_reason_id">' ).val( result )
                            );
                        form.submit();
                    }
                });

            })

            .on( 'confirmed', '[data-status="cancel"]', function ( e, pe )
            {

                e.preventDefault();
                pe.preventDefault();

                var form = $( pe.target );

                if ( $( this ).hasClass( 'submit-loading' ) )
                {
                    $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                }

                var id = $( this ).attr( 'data-id' );

                bootbox.prompt({
                    title: 'Укажите причину отмены заявки',
                    inputType: 'textarea',
                    callback: function ( result )
                    {
                        if ( ! result )
                        {
                            alert( 'Действие отменено!' );
                        }
                        else
                        {
                            form
                                .removeAttr( 'data-confirm' )
                                .append(
                                    $( '<input type="hidden" name="comment">' ).val( result )
                                );
                            form.submit();
                        }
                    }
                });

            })

            @if ( $need_act )
                .on( 'confirmed', '[data-status="completed_with_act"]', function ( e, pe )
                {

                    e.preventDefault();
                    pe.preventDefault();

                    if ( $( this ).hasClass( 'submit-loading' ) )
                    {
                        $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                    }

                    var form = $( pe.target );

                    var model_name = form.find( '[name="model_name"]' ).val();
                    var model_id = form.find( '[name="model_id"]' ).val();
                    var status = form.find( '[name="status_code"]' ).val();

                    $.get( '/file', {
                        model_name: model_name,
                        model_id: model_id,
                        status: status
                    }, function ( response )
                    {
                        Modal.createSimple( 'Прикрепить оформленный Акт', response, 'file' );
                    });

                })
            @endif

            .on( 'confirmed', '[data-status="waiting"]', function ( e, pe )
            {

                e.preventDefault();
                pe.preventDefault();

                if ( $( this ).hasClass( 'submit-loading' ) )
                {
                    $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                }

                var form = $( pe.target );

                var model_name = form.find( '[name="model_name"]' ).val();
                var model_id = form.find( '[name="model_id"]' ).val();
                var status_code = form.find( '[name="status_code"]' ).val();

                $.get( '{{ route( 'tickets.postpone', $ticket->id ) }}', {
                    model_name: model_name,
                    model_id: model_id,
                    status_code: status_code
                }, function ( response )
                {
                    Modal.createSimple( 'Отложить заявку до', response, 'postpone' );
                    Modal.onSubmit = function ( e, id )
                    {
                        if ( id == 'postpone' )
                        {
                            e.preventDefault();
                            var thisForm = $( this );
                            form
                                .removeAttr( 'data-confirm' )
                                .append(
                                    $( '<input type="hidden" name="postponed_to">' ).val( thisForm.find( '[name="postponed_to"]' ).val() ),
                                    $( '<input type="hidden" name="postponed_comment">' ).val( thisForm.find( '[name="postponed_comment"]' ).val() )
                                );
                            form.submit();
                        }
                    };
                });

            });

    </script>

@endsection
