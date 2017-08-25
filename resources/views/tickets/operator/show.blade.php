@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row">
        <div class="col-lg-6">

            @if ( $ticket->status_code == 'closed_with_confirm' )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        @include( 'parts.rate_form' )
                    </div>
                </div>
            @endif

            @if ( $ticket->getAvailableStatuses() )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <dl>
                                <dt>Сменить статус:</dt>
                                <dd>
                                    @foreach( $ticket->getAvailableStatuses() as $status_code => $status_name )
                                        {!! Form::open( [ 'url' => route( 'tickets.status', $ticket->id ), 'data-status' => $status_code, 'data-id' => $ticket->id, 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $status_name . '"?' ] ) !!}
                                        {!! Form::hidden( 'status_code', $status_code ) !!}
                                        {!! Form::submit( $status_name, [ 'class' => 'btn btn-primary' ] ) !!}
                                        {!! Form::close() !!}
                                    @endforeach
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xs-6">
                    <div class="note note-{{ $ticket->getClass() }}">
                        <dl>
                            <dt>Статус:</dt>
                            <dd>{{ $ticket->status_name }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="type">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Тип обращения:</dt>
                            <dd>{{ $ticket->type->name ?? '' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="address">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Адрес проблемы:</dt>
                            <dd>
								{{ $ticket->getAddress() }}
								<span class="small text-muted">
									({{ $ticket->getPlace() }})
								</span>
							</dd>
                        </dl>
                    </div>
                </div>
				<div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="mark">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Дополнительные метки:</dt>
                            <dd>
                                @if ( $ticket->type && ( $ticket->type->is_pay || $ticket->type->category->is_pay ) )
                                    <span class="badge badge-warning bold">
                                        Платно
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->emergency )
                                    <span class="badge badge-danger bold">
                                        Авария
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->urgently )
                                    <span class="badge badge-danger bold">
                                        Срочно
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->dobrodel )
                                    <span class="badge badge-danger bold">
                                        Добродел
                                    </span>
                                @endif
                                @if ( $ticket->group_uuid )
                                    <a href="{{ route( 'tickets.index' ) }}?group={{ $ticket->group_uuid }}" class="badge badge-info bold">
                                        Сгруппировано
                                    </a>
                                    &nbsp;
                                @endif
                                &nbsp;
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
				<div class="col-xs-12">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="text">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Текст обращения:</dt>
                            <dd>{{ $ticket->text }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
			
            <hr />

			@if ( $ticket->type )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <strong>Сезонность устранения: </strong>
                        {{ $ticket->type->season }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Период на принятие заявки в работу, час:</dt>
                            <dd>{{ $ticket->type->period_acceptance }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Период на исполнение, час:</dt>
                            <dd>{{ $ticket->type->period_execution }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
			@endif

            @if ( $dt_transferred )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <strong>Заявка передана ЭО: </strong>
                            {{ $dt_transferred->format( 'd.m.Y H:i' ) }}
                        </div>
                    </div>
                </div>
            @endif

            @if ( $dt_acceptance_expire && $dt_execution_expire )
                <div class="row">
                    <div class="col-xs-6">
                        @if ( $dt_accepted )
                            <div class="note note-{{ $dt_accepted->timestamp > $dt_acceptance_expire->timestamp ? 'danger' : 'success' }}">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Принять до:</dt>
                                            <dd>
                                                {{ $dt_acceptance_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Принято:</dt>
                                            <dd>
                                                {{ $dt_accepted->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_accepted->timestamp > $dt_acceptance_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="note {{ $dt_now->timestamp > $dt_acceptance_expire->timestamp ? 'note-danger' : '' }}">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <dl>
                                            <dt>Принять до:</dt>
                                            <dd>
                                                {{ $dt_acceptance_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_now->timestamp > $dt_acceptance_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-xs-6">
                        @if ( $dt_completed )
                            <div class="note note-{{ $dt_completed->timestamp > $dt_execution_expire->timestamp ? 'danger' : 'success' }}">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Выполнить до:</dt>
                                            <dd>
                                                {{ $dt_execution_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Выполнено:</dt>
                                            <dd>
                                                {{ $dt_completed->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_completed->timestamp > $dt_execution_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="note {{ $dt_now->timestamp > $dt_execution_expire->timestamp ? 'note-danger' : '' }}">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <dl>
                                            <dt>Выполнить до:</dt>
                                            <dd>
                                                {{ $dt_execution_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_now->timestamp > $dt_execution_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            @if ( $execution_hours )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <b>Продолжительность работы ЭО в часах: </b>
                            {{ $execution_hours }}
                        </div>
                    </div>
                </div>
            @endif

            @if ( $ticket->rate )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <b>Оценка работы ЭО: </b>
                            @include( 'parts.rate', [ 'ticket' => $ticket ] )
                            @if ( $ticket->rate_comment )
                                <p>
                                    {{ $ticket->rate_comment }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <div class="col-lg-6">

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="name">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>ФИО Заявителя:</dt>
                            <dd>{{ $ticket->getName() }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="phone">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Телефон(ы) Заявителя:</dt>
                            <dd>{{ $ticket->getPhones() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <dl>
                            <dt>Адрес проживания:</dt>
                            <dd>
                                {{ $ticket->customer ? $ticket->customer->getAddress() : '' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <hr />

            @if ( $ticket->type && $ticket->type->need_act )
                <div class="alert alert-warning">
                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                    Требуется Акт выполненных работ
                </div>
            @endif

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
						@if ( $ticket->canEdit() )
							<button type="button" class="btn pull-right btn-primary margin-right-10 hidden-print" data-action="add-management">
								<i class="fa fa-plus"></i>
							</button>
						@endif
						<h4>
							Эксплуатационные организации
						</h4>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        Наименование \ Телефон \ Адрес
                                    </th>
									@if ( $ticket->canEdit() )
										<th>
											&nbsp;
										</th>
									@else
										<th>
											Исполнитель
										</th>
										<th>
											Статус
										</th>
									@endif
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ( $ticket->managements as $ticketManagement )
                                <tr>
                                    <td>
                                        <dl>
                                            <dt>
                                                {{ $ticketManagement->management->name }}
                                            </dt>
                                            <dd>
                                                {{ $ticketManagement->management->phone }}
                                            </dd>
                                            <dd class="small">
                                                {{ $ticketManagement->management->address }}
                                            </dd>
                                        </dl>
                                        @if ( $ticket->type->need_act && $ticketManagement->status_code )
                                            <p class="margin-top-10 hidden-print">
                                                <a href="{{ route( 'tickets.act', $ticketManagement->id ) }}" class="btn btn-info">
                                                    <i class="glyphicon glyphicon-print"></i>
                                                    Акт выполненных работ
                                                </a>
                                            </p>
                                        @endif
                                    </td>
									@if ( $ticket->canEdit() )
										<td class="text-right" style="padding-right: 0px !important;">
											<button type="button" class="btn btn-xs btn-danger margin-right-10 hidden-print" data-delete-management="{{ $ticketManagement->id }}">
												<i class="fa fa-remove"></i>
											</button>
										</td>
									@else
										<td>
											{{ $ticketManagement->executor ?? '-' }}
										</td>
										<td>
											<span class="text-{{ $ticketManagement->getClass() }}">
												{{ $ticketManagement->status_name }}
											</span>
										</td>
									@endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ( $ticket->comments->count() )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <h4>Комментарии</h4>
                            @include( 'parts.comments', [ 'ticket' => $ticket, 'comments' => $ticket->comments ] )
                        </div>
                    </div>
                </div>
            @endif

            @if ( $ticket->canComment() )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-block btn-primary btn-lg" data-action="comment" data-model-name="{{ get_class( $ticket ) }}" data-model-id="{{ $ticket->id }}" data-origin-model-name="{{ get_class( $ticket ) }}" data-origin-model-id="{{ $ticket->id }}" data-file="1">
                            <i class="fa fa-commenting"></i>
                            Добавить комментарий
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
	
	{!! Form::hidden( 'ticket_id', $ticket->id, [ 'id' => 'ticket-id' ] ) !!}

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

    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )

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

            })
			
			.on( 'click', '[data-edit]', function ( e )
			{
				e.preventDefault();
				var param = $( this ).attr( 'data-edit' );
				$.get( '{{ route( 'tickets.edit', $ticket ) }}', {
					param: param
				}, function ( response )
				{
					Modal.createSimple( 'Редактировать обращение', response, 'edit-' + param );
				});
			})
			
			.on( 'click', '[data-action="add-management"]', function ( e )
			{
				e.preventDefault();
				$.get( '{{ route( 'tickets.add_management', $ticket ) }}', 
				function ( response )
				{
					Modal.createSimple( 'Добавить Эксплуатационную организацию', response, 'add-management' );
				});
			})
			
			.on( 'click', '[data-delete-management]', function ( e )
			{
				e.preventDefault();
				var line = $( this ).closest( 'tr' );
				var id = $( this ).attr( 'data-delete-management' );
				bootbox.confirm({
					message: 'Вы уверены, что хотите убрать из обращения ЭО?',
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

            });

    </script>

@endsection