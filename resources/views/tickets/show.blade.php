@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row">
        <div class="col-lg-6">

            @if ( $ticketManagement && \Auth::user()->can( 'tickets.rate' ) && $ticketManagement->status_code == 'closed_with_confirm' )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        @include( 'parts.rate_form', [ 'ticketManagement' => $ticketManagement ] )
                    </div>
                </div>
            @endif

            @if ( $ticket->getAvailableStatuses() || ( $ticketManagement && $ticketManagement->getAvailableStatuses() ) )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <dl>
                                <dt>Сменить статус:</dt>
                                <dd>
                                    @if ( $ticket->getAvailableStatuses() )
                                        @foreach( $ticket->getAvailableStatuses() as $status_code => $status_name )
                                            {!! Form::open( [ 'url' => route( 'tickets.status', $ticket->id ), 'data-status' => $status_code, 'data-id' => $ticket->id, 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $status_name . '"?' ] ) !!}
                                            {!! Form::hidden( 'model_name', get_class( $ticket ) ) !!}
                                            {!! Form::hidden( 'model_id', $ticket->id ) !!}
                                            {!! Form::hidden( 'status_code', $status_code ) !!}
                                            {!! Form::hidden( 'comment', '' ) !!}
                                            {!! Form::submit( $status_name, [ 'class' => 'btn btn-primary' ] ) !!}
                                            {!! Form::close() !!}
                                        @endforeach
                                    @endif
                                    @if ( $ticketManagement && $ticketManagement->getAvailableStatuses() )
                                        @foreach( $ticketManagement->getAvailableStatuses() as $status_code => $status_name )
                                            {!! Form::open( [ 'url' => route( 'tickets.status', $ticketManagement->getTicketNumber() ), 'data-status' => $status_code, 'data-id' => $ticketManagement->id, 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $status_name . '"?' ] ) !!}
                                            {!! Form::hidden( 'model_name', get_class( $ticketManagement ) ) !!}
                                            {!! Form::hidden( 'model_id', $ticketManagement->id ) !!}
                                            {!! Form::hidden( 'status_code', $status_code ) !!}
                                            {!! Form::hidden( 'comment', '' ) !!}
                                            {!! Form::submit( $status_name, [ 'class' => 'btn btn-primary' ] ) !!}
                                            {!! Form::close() !!}
                                        @endforeach
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xs-6">
                    <div class="note note-{{ ( $ticketManagement ?? $ticket )->getClass() }}">
                        <dl>
                            <dt>Статус:</dt>
                            <dd>
                                @if ( $ticketManagement )
                                    @if ( \Auth::user()->can( 'tickets.history' ) )
                                        <a href="{{ route( 'tickets.history', $ticketManagement->getTicketNumber() ) }}">
                                            {{ $ticketManagement->status_name }}
                                        </a>
                                    @else
                                        {{ $ticketManagement->status_name }}
                                    @endif
                                @else
                                    {{ $ticket->status_name }}
                                @endif
                            </dd>
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
                            <dt>Тип заявки:</dt>
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
                            <dt>Текст заявки:</dt>
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

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Заявка передана в ЭО:</dt>
                            <dd>{{ $dt_transferred ? $dt_transferred->format( 'd.m.Y H:i' ) : '-' }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Оператор ЕДС:</dt>
                            <dd>{{ $ticket->author->getName() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

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

            @if ( $ticketManagement && $ticketManagement->rate )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <b>Оценка работы ЭО: </b>
                            @include( 'parts.rate', [ 'ticketManagement' => $ticketManagement ] )
                            @if ( $ticketManagement->rate_comment )
                                <p>
                                    {{ $ticketManagement->rate_comment }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ( \Auth::user()->can( 'calls' ) && $ticket->call() && $ticket->call()->hasMp3() )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <a href="{{ $ticket->call()->getMp3() }}" target="_blank">
                                <i class="fa fa-phone"></i>
                                Запись разговора
                            </a>
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
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print" data-edit="actual_address">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Адрес проживания:</dt>
                            <dd>
                                {{ $ticket->getActualAddress() ?: '-' }}
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

            @if ( $ticketManagement )

                <div class="row">
                    <div class="col-lg-6">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->managements()->mine()->count() > 1 )
                                        <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                                            Эксплуатационная организация:
                                        </a>
                                    @else
                                        Эксплуатационная организация:
                                    @endif
                                </dt>
                                <dd>
                                    {{ $ticketManagement->management->name ?: '-' }}
                                </dd>
                                <dd>
                                    {{ $ticketManagement->management->phone }}
                                </dd>
                                <dd class="small">
                                    {{ $ticketManagement->management->address }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    @if ( $ticketManagement->executor )
                        <div class="col-lg-6">
                            <div class="note note-info">
                                <dl>
                                    <dt>Исполнитель:</dt>
                                    <dd>
                                        {{ $ticketManagement->executor }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>

                @if ( $ticketManagement->canPrintAct() || $ticketManagement->canUploadAct() )
                    <div class="row hidden-print">
                        <div class="col-xs-12">
                            <div class="note">
                                @if ( $ticketManagement->canPrintAct() )
                                    <a href="{{ route( 'tickets.act', $ticketManagement->getTicketNumber() ) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="glyphicon glyphicon-print"></i>
                                        Распечатать бланк Акта
                                    </a>
                                @endif
                                @if ( $ticketManagement->canUploadAct() )
                                    <button class="btn btn-sm btn-primary" data-action="file" data-model-name="{{ get_class( $ticketManagement ) }}" data-model-id="{{ $ticketManagement->id }}" data-title="Прикрепить оформленный акт" data-status="completed_with_act">
                                        <i class="glyphicon glyphicon-upload"></i>
                                        Прикрепить оформленный Акт
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if ( $ticketManagement->files->count() )
                    <div class="note note-default">
                        @foreach ( $ticketManagement->files as $file )
                            <div>
                                <a href="{{ route( 'files.download', [ 'id' => $file->id, 'token' => $file->getToken() ] ) }}">
                                    <i class="fa fa-file"></i>
                                    {{ $file->name }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

            @else

                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <div class="bold">
                                Эксплуатационные организации
                            </div>
                            <div class="row">
                                <div class="col-xs-5 bold small">
                                    Наименование \ Телефон \ Адрес
                                </div>
                                @if ( ! $ticket->canEdit() )
                                    <div class="col-xs-4 bold small">
                                        Исполнитель
                                    </div>
                                    <div class="col-xs-3 bold small text-right">
                                        Статус
                                    </div>
                                @endif
                            </div>
                            @foreach ( $ticket->managements()->mine()->get() as $_ticketManagement )
                                <hr />
                                <div class="row">
                                    <div class="col-xs-5">
                                        <dl>
                                            @if ( ! $_ticketManagement->management->has_contract )
                                                <div class="label label-danger pull-right">
                                                    Отсутствует договор
                                                </div>
                                            @endif
                                            <dt>
                                                <a href="{{ route( 'tickets.show', $_ticketManagement->getTicketNumber() ) }}">
                                                    {{ $_ticketManagement->management->name }}
                                                </a>
                                            </dt>
                                            <dd class="small">
                                                {{ $_ticketManagement->management->getPhones() }}
                                            </dd>
                                            <dd class="small">
                                                {{ $_ticketManagement->management->address }}
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-xs-4">
                                        {{ $_ticketManagement->executor ?? '-' }}
                                    </div>
                                    <div class="col-xs-3 text-right">
                                        <span class="badge badge-{{ $_ticketManagement->getClass() }} bold">
                                            {{ $_ticketManagement->status_name }}
                                        </span>
                                    </div>
                                </div>
                                @if ( $_ticketManagement->canPrintAct() )
                                    <div class="row margin-top-10 hidden-print">
                                        <div class="col-xs-12">
                                            @if ( $_ticketManagement->canPrintAct() )
                                                <a href="{{ route( 'tickets.act', $_ticketManagement->getTicketNumber() ) }}" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="glyphicon glyphicon-print"></i>
                                                    Распечатать бланк Акта
                                                </a>
                                            @endif
                                            @if ( $_ticketManagement->canUploadAct() )
                                                <button class="btn btn-sm btn-primary" data-action="file" data-model-name="{{ get_class( $_ticketManagement ) }}" data-model-id="{{ $_ticketManagement->id }}" data-title="Прикрепить оформленный Акт">
                                                    <i class="glyphicon glyphicon-upload"></i>
                                                    Прикрепить оформленный Акт
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if ( $_ticketManagement->files->count() )
                                    <div class="note note-default">
                                        @foreach ( $_ticketManagement->files as $file )
                                            <div>
                                                <a href="{{ route( 'files.download', [ 'id' => $file->id, 'token' => $file->getToken() ] ) }}">
                                                    <i class="fa fa-file"></i>
                                                    {{ $file->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

            @endif

            @if ( $ticketManagement && \Auth::user()->can( 'tickets.comments' ) && $ticketManagement->comments->count() )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <h4>Комментарии</h4>
                            @include( 'parts.comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $ticketManagement->comments ] )
                        </div>
                    </div>
                </div>
            @endif

            @if ( $ticketManagement && $ticketManagement->canComment() )
                <div class="row hidden-print">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-block btn-primary btn-lg" data-action="comment" data-model-name="{{ get_class( $ticketManagement ) }}" data-model-id="{{ $ticketManagement->id }}" data-origin-model-name="{{ get_class( $ticketManagement ) }}" data-origin-model-id="{{ $ticketManagement->id }}" data-file="1">
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
	<script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
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
					message: 'Вы уверены, что хотите убрать из заявки ЭО?',
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

                bootbox.prompt({
                    title: 'Введите ФИО и должность назначенного исполнителя',
                    inputType: 'textarea',
                    callback: function ( result )
                    {
                        if ( ! result )
                        {
                            alert( 'Действие отменено!' );
                        }
                        else
                        {
                            $.post( '{{ route( 'tickets.executor' ) }}', {
                                id: id,
                                executor: result
                            }, function ( response )
                            {
                                bootbox.alert({
                                    message: 'Исполнитель успешно назначен',
                                    size: 'small',
                                    callback: function ()
                                    {
                                        window.location.reload();
                                    }
                                })
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