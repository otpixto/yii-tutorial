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
        .note {
            margin: 5px 0;
        }
        .d-inline {
            display: inline;
        }
        #customer_tickets table *, #neighbors_tickets table * {
            font-size: 12px;
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

    <script type="text/javascript">

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

            function setExecutor ()
            {
                $.get( '{{ route( 'tickets.executor', $ticketManagement->id ) }}', function ( response )
                {
                    Modal.createSimple( 'Назначить исполнителя', response, 'executor' );
                });
            };

        @endif

        $( document )

            .ready( function ()
            {

                $( '#tags' ).on( 'itemAdded', function ( e )
                {
                    var id = $( '#ticket-id' ).val();
                    var tag = e.item;
                    if ( ! id || ! tag ) return;
                    $.post( '{{ route( 'tickets.tags.add' ) }}', {
                        id: id,
                        tag: tag
                    });
                });

                $( '#tags' ).on( 'itemRemoved', function ( e )
                {
                    var id = $( '#ticket-id' ).val();
                    var tag = e.item;
                    if ( ! id || ! tag ) return;
                    $.post( '{{ route( 'tickets.tags.del' ) }}', {
                        id: id,
                        tag: tag
                    });
                });

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

            })

            .on ( 'click', '.nav-tabs a', function ( e )
            {
                $( this ).tab( 'show' );
                switch ( $( this ).attr( 'href' ) )
                {

                    case '#customer_tickets':
                        $( '#customer_tickets' ).loading();
                        $.get( '{{ route( 'tickets.customers', $ticket->id ) }}', function ( response )
                        {
                            $( '#customer_tickets' ).html( response );
                        });
                        break;

                    case '#neighbors_tickets':
                        $( '#neighbors_tickets' ).loading();
                        $.get( '{{ route( 'tickets.neighbors', $ticket->id ) }}', function ( response )
                        {
                            $( '#neighbors_tickets' ).html( response );
                        });
                        break;

                    case '#works':
                        $( '#works' ).loading();
                        $.get( '{{ route( 'tickets.works', $ticket->id ) }}', function ( response )
                        {
                            $( '#works' ).html( response );
                        });
                        break;

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
                                        form
                                            .append(
                                                $( '<input type="hidden" name="delayed_to" />' ).val( result )
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
				switch ( param )
                {
                    case 'executor':
                        setExecutor();
                        break;
                    default:
                        $.get( '{{ route( 'tickets.edit', $ticket ) }}', {
                            param: param
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
                    Modal.onSubmit = function ( e )
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
                    };
                });

            });

    </script>

@endsection