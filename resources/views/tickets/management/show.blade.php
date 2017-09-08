@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( $ticketManagement->status_code == 'accepted' )
        {!! Form::open( [ 'url' => route( 'tickets.managements.executor', $ticketManagement->id ), 'class' => 'submit-loading form-horizontal' ] ) !!}
        <div class="note note-info hidden-print">
            <div class="form-group">
                {!! Form::label( 'executor', 'Назначить исполнителя:', [ 'class' => 'control-label col-xs-4' ] ) !!}
                <div class="col-xs-4">
                    {!! Form::text( 'executor', $ticketManagement->executor, [ 'class' => 'form-control', 'required' ] ) !!}
                </div>
                <div class="col-xs-4">
                    {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    @endif

    @if ( $ticketManagement->getAvailableStatuses() )
        <div class="row hidden-print">
            <div class="col-xs-12">
                <div class="note note-info">
                    <dl>
                        <dt>Сменить статус:</dt>
                        <dd>
                            @foreach( $ticketManagement->getAvailableStatuses() as $status_code => $status_name )
                                {!! Form::open( [ 'url' => route( 'tickets.managements.status', $ticketManagement->id ), 'data-status' => $status_code, 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $status_name . '"?' ] ) !!}
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
        <div class="col-lg-6">

            <div class="row">
                <div class="col-xs-6">
                    <div class="note note-{{ $ticket->getClass() }}">
                        <dl>
                            <dt>Статус:</dt>
                            <dd>{{ $ticketManagement->status_name }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Тип заявки:</dt>
                            <dd>{{ $ticket->type->name }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Адрес проблемы:</dt>
                            <dd>{{ $ticket->getAddress() }}</dd>
                        </dl>
                    </div>
                </div>
				<div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Проблемное место:</dt>
                            <dd>{{ $ticket->getPlace() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
				<div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Текст обращения:</dt>
                            <dd>{{ $ticket->text }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Дополнительные метки:</dt>
                            <dd>
                                @if ( $ticket->type->is_pay || $ticket->type->category->is_pay )
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
                                &nbsp;
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
			
            <hr />

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

        </div>
        <div class="col-lg-6">
		
			<hr class="visible-md" />

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>ФИО Заявителя:</dt>
                            <dd>{{ $ticket->getName() }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Телефон(ы) Заявителя:</dt>
                            <dd>{{ $ticket->getPhones() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row hidden">
                <div class="col-xs-12">
                    <div class="note">
                        <dl>
                            <dt>Адрес проживания:</dt>
                            <dd>
                                {{ $ticket->customer->getAddress() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <hr />

			@if ( $ticket->type->need_act )
				<div class="row">
					<div class="col-xs-12">
						<div class="alert alert-warning">
							<i class="glyphicon glyphicon-exclamation-sign"></i>
							Требуется Акт выполненных работ
							<p class="margin-top-10 hidden-print">
								<a href="{{ route( 'tickets.act', $ticketManagement->id ) }}" class="btn btn-info">
									<i class="glyphicon glyphicon-print"></i>
									Акт выполненных работ
								</a>
							</p>
						</div>
					</div>
				</div>
			@endif
			
			@if ( $ticketManagement->executor )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <dl>
                                <dt>Исполнитель:</dt>
                                <dd>
                                    {{ $ticketManagement->executor }}
                                </dd>
                            </dl>
                        </div>
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

            @if ( $ticketManagement->canComment() )
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
    </style>
@endsection