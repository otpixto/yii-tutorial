@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Обращение #' . $ticket->id ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( $ticketManagement->getAvailableStatuses() )
        <div class="row">
            <div class="col-xs-12">
                <div class="note note-info">
                    <dl>
                        <dt>Сменить статус:</dt>
                        <dd>
                            @foreach( $ticketManagement->getAvailableStatuses() as $status_code => $status_name )
                                {!! Form::open( [ 'url' => route( 'tickets.managements.status', $ticketManagement->id ), 'class' => 'd-inline submit-loading form-horizontal' ] ) !!}
                                {!! Form::hidden( 'status_code', $status_code ) !!}
                                {!! Form::submit( $status_name, [ 'class' => 'btn btn-primary btn-lg' ] ) !!}
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
                            <dt>Тип обращения:</dt>
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
                            <dd>{{ $ticket->address }}</dd>
                        </dl>
                    </div>
                </div>
				<div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Проблемное место:</dt>
                            <dd>{{ $ticket->place }}</dd>
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

        </div>
        <div class="col-lg-6">

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
                            <dt>Адрес проживания:</dt>
                            <dd>{{ $ticket->actual_address ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <hr />

            @if ( $ticket->type->need_act )
                <div class="alert alert-warning">
                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                    Требуется Акт выполненных работ
                </div>
            @endif

            <p class="margin-top-10 hidden-print">
                <a href="{{ route( 'tickets.act', $ticketManagement->id ) }}" class="btn btn-info">
                    <i class="glyphicon glyphicon-print"></i>
                    Акт выполненных работ
                </a>
            </p>

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <h4>Проделанная работа</h4>
                        @if ( $ticket->comments->count() )
                            @include( 'parts.comments', [ 'comments' => $ticket->comments ] )
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="button" class="btn btn-block btn-primary btn-lg">
                        Добавить запись о проделанной работе
                    </button>
                </div>
            </div>

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