@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div id="ticket-show">

        @include( 'parts.ticket_info' )

    </div>

	{!! Form::hidden( 'ticket_id', $ticket->id, [ 'id' => 'ticket-id' ] ) !!}
    {!! Form::hidden( 'management_id', isset( $ticketManagement ) ? $ticketManagement->management_id : null, [ 'id' => 'management-id' ] ) !!}

@endsection

@section( 'css' )
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
	<script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
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

        $( document )

            .ready( function ()
            {

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

					}
				});

            })

			.on( 'click', '[data-edit]', function ( e )
			{
				e.preventDefault();
				var param = $( this ).attr( 'data-edit' );
				$.get( '{{ route( 'tickets.edit', $ticket ) }}', {
					param: param
				}, function ( response )
				{
					Modal.createSimple( 'Редактировать заявку', response, 'edit-' + param );
				});
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

					var dialog = bootbox.dialog({
						title: 'Выберите исполнителя',
						message: '<p><i class="fa fa-spin fa-spinner"></i> Загрузка... </p>'
					});

					dialog.init( function ()
					{
						$.get( '{{ route( 'tickets.executor', $ticketManagement->id ) }}', function ( response )
						{
							dialog.find( '.bootbox-body' ).html( response );
							dialog.removeAttr( 'tabindex' );
							dialog.find( '.select2' ).select2();
						});
					});

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
                                .find( '[name="comment"]' ).val( result );
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
                                .find( '[name="comment"]' ).val( result );
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

            });

    </script>

@endsection