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
	<link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
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
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-repeater/jquery.repeater.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mt-repeater' ).repeater({
                    show: function ()
                    {
                        $( this ).slideDown();
                    },
                    hide: function ( deleteElement )
                    {
                        if ( confirm( 'Уверены, что хотите удалить строку?' ) )
                        {
                            $( this ).slideUp( deleteElement );
                        }
                    },
                    ready: function ( setIndexes )
                    {

                    },
                    defaultValues: {
                        quantity: 1
                    }
                });

            })

            .on( 'click', '[data-rate]', function ( e )
            {

                e.preventDefault();

                var rate = $( this ).attr( 'data-rate' );
                var form = $( '#rate-form' );

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

			{{--.on( 'click', '[data-action="add-management"]', function ( e )--}}
			{{--{--}}
				{{--e.preventDefault();--}}
				{{--$.get( '{{ route( 'tickets.add_management', $ticket ) }}', --}}
				{{--function ( response )--}}
				{{--{--}}
					{{--Modal.createSimple( 'Добавить Эксплуатационную организацию', response, 'add-management' );--}}
				{{--});--}}
			{{--})--}}

			.on( 'click', '[data-delete-management]', function ( e )
			{
				e.preventDefault();
				var line = $( this ).closest( 'tr' );
				var id = $( this ).attr( 'data-delete-management' );
				bootbox.confirm({
					message: 'Вы уверены, что хотите убрать из заявки УО?',
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

							$.post( '{{ route( 'tickets.del_management' ) }}', {
								    id: id
								},
								function ( response )
								{
									line.remove();
								});

						}
					}
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

                var id = $( this ).attr( 'data-id' );

                var dialog = bootbox.dialog({
                    title: 'Выберите исполнителя',
                    message: '<p><i class="fa fa-spin fa-spinner"></i> Загрузка... </p>'
                });

                dialog.init( function ()
                {
                    $.get( '{{ route( 'tickets.executor' ) }}', {
                        id: id
                    }, function ( response )
                    {
                        dialog.find( '.bootbox-body' ).html( response );
                        dialog.removeAttr( 'tabindex' );
                        dialog.find( '.select2' ).select2();
                    });
                });

            })

            .on( 'confirmed', '[data-status="closed_with_confirm"]', function ( e, pe )
            {

                e.preventDefault();
                pe.preventDefault();

                if ( $( this ).hasClass( 'submit-loading' ) )
                {
                    $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
                }

                var id = $( this ).attr( 'data-id' );

                var dialog = bootbox.dialog({
                    title: 'Оцените работу УО',
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