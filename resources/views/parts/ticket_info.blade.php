<div class="row">
    <div class="col-lg-6">

        @if ( $ticketManagement && $ticketManagement->canRate() )
            <div class="row hidden-print">
                <div class="col-xs-12">
                    @include( 'parts.rate_form', [ 'ticketManagement' => $ticketManagement ] )
                </div>
            </div>
        @endif

        @if ( count( $availableStatuses ) )
            <div class="row hidden-print">
                <div class="col-xs-12">
                    <div class="note note-info">
                        <dl>
                            <dt>Сменить статус:</dt>
                            <dd>
                                @foreach( $availableStatuses as $status_code => $availableStatus )
                                    {!! Form::open( [ 'url' => $availableStatus[ 'url' ], 'data-status' => $status_code, 'data-id' => $availableStatus[ 'model_id' ], 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $availableStatus[ 'status_name' ] . '"?' ] ) !!}
                                    {!! Form::hidden( 'model_name', $availableStatus[ 'model_name' ] ) !!}
                                    {!! Form::hidden( 'model_id', $availableStatus[ 'model_id' ] ) !!}
                                    {!! Form::hidden( 'status_code', $status_code ) !!}
                                    {!! Form::hidden( 'comment', '' ) !!}
                                    {!! Form::submit( $availableStatus[ 'status_name' ], [ 'class' => 'btn btn-primary margin-bottom-5 margin-right-5' ] ) !!}
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
                            @if ( $ticket->getPlace() )
                                <span class="small text-muted">
                                            ({{ $ticket->getPlace() }})
                                        </span>
                            @endif
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
                                    <i class="icon-fire"></i>
                                    Авария
                                </span>
                                &nbsp;
                            @endif
                            @if ( $ticket->urgently )
                                <span class="badge badge-danger bold">
                                    <i class="icon-speedometer"></i>
                                    Срочно
                                </span>
                                &nbsp;
                            @endif
                            @if ( $ticket->dobrodel )
                                <span class="badge badge-danger bold">
                                    <i class="icon-heart"></i>
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
                        <dt>Заявка передана в УО:</dt>
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
                        <b>Продолжительность работы УО в часах: </b>
                        {{ $execution_hours }}
                    </div>
                </div>
            </div>
        @endif

        @if ( $ticketManagement && $ticketManagement->rate )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <b>Оценка работы УО: </b>
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

        @if ( ( \Auth::user()->can( 'calls.all' ) || ( \Auth::user()->can( 'calls.my' ) && \Auth::user()->id == $ticket->author_id ) ) && $ticket->cdr && $ticket->cdr->hasMp3() )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <a href="{{ $ticket->cdr->getMp3() }}" target="_blank">
                            <i class="fa fa-chevron-circle-down text-success"></i>
                            Входящий вызов
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if ( $ticketCalls->count() )
            @foreach ( $ticketCalls as $ticketCall )
                @if ( $ticketCall->cdr && $ticketCall->cdr->hasMp3() )
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="note">
                                <a href="{{ $ticketCall->cdr->getMp3() }}" target="_blank">
                                    <i class="fa fa-chevron-circle-up text-danger"></i>
                                    Исходящий вызов
                                    <span class="text-muted small">
                                                {{ $ticketCall->created_at->format( 'd.m.Y H:i' ) }}
                                            </span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
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
                    @if ( $ticket->canCall() )
                        <button type="button" class="btn btn-lg btn-warning pull-right margin-left-10 hidden-print" data-action="ticket-call" data-ticket="{{ $ticket->id }}" data-phones="{{ $ticket->getPhones() }}">
                            <i class="fa fa-phone"></i>
                        </button>
                    @endif
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
                                        Управляющая организация:
                                    </a>
                                @else
                                    Управляющая организация:
                                @endif
                            </dt>
                            <dd>
                                {{ $ticketManagement->management->name ?: '-' }}
                            </dd>
                            <dd>
                                {{ $ticketManagement->management->phone }}
                            </dd>
                            @if ( $ticketManagement->management->address )
                                <dd class="small">
                                    {{ $ticketManagement->management->address->name }}
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>
                @if ( $ticketManagement->executor )
                    <div class="col-lg-6">
                        <div class="note note-info">
                            <dl>
                                <dt>Исполнитель:</dt>
                                <dd>
                                    {{ $ticketManagement->executor->name }}
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
                            Управляющие организации
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
										@if ( $_ticketManagement->management->address )
											<dd class="small">
												{{ $_ticketManagement->management->address->name }}
											</dd>
										@endif
                                    </dl>
                                </div>
                                <div class="col-xs-4">
                                    <span class="small text-info bold">
                                        {{ $_ticketManagement->executor ? $_ticketManagement->executor->name : '-' }}
                                    </span>
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

        @if ( $ticketManagement && \Auth::user()->can( 'tickets.works.show' ) )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note note-info">
                        <h4>Выполненные работы</h4>
                        <div class="row margin-bottom-10">
                            <label class="col-xs-9 control-label text-muted">Наименование</label>
                            <label class="col-xs-2 control-label text-muted">Кол-во</label>
                        </div>
                        @if ( \Auth::user()->can( 'tickets.works.edit' ) )
                            {!! Form::open( [ 'method' => 'post', 'class' => 'submit-loading' ] ) !!}
                            <div class="mt-repeater">
                                <div data-repeater-list="works">
                                    @if ( $ticketManagement->works->count() )
                                        @foreach ( $ticketManagement->works as $work )
                                            <div data-repeater-item="" class="row margin-bottom-10">
                                                <div class="col-xs-9">
                                                    {!! Form::hidden( 'id', $work->id ) !!}
                                                    <input type="text" name="name" placeholder="Наименование" value="{{ $work->name }}" class="form-control" required="required" />
                                                </div>
                                                <div class="col-xs-2">
                                                    <input type="text" name="quantity" placeholder="Кол-во" class="form-control" value="{{ $work->quantity }}" required="required" />
                                                </div>
                                                <div class="col-xs-1 text-right hidden-print">
                                                    <button type="button" data-repeater-delete="" class="btn btn-danger">
                                                        <i class="fa fa-close"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div data-repeater-item="" class="row margin-bottom-10 hidden-print">
                                            <div class="col-xs-9">
                                                {!! Form::hidden( 'id', null ) !!}
                                                <input type="text" name="name" placeholder="Наименование" class="form-control" required="required" />
                                            </div>
                                            <div class="col-xs-2">
                                                <input type="text" name="quantity" placeholder="Кол-во" class="form-control" value="1" required="required" />
                                            </div>
                                            <div class="col-xs-1 text-right hidden-print">
                                                <button type="button" data-repeater-delete="" class="btn btn-danger">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <hr class="hidden-print" />
                                <div class="row hidden-print">
                                    <div class="col-xs-6">
                                        <button type="button" data-repeater-create="" class="btn btn-sm btn-default mt-repeater-add">
                                            <i class="fa fa-plus"></i>
                                            Добавить
                                        </button>
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa fa-check"></i>
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        @else
                            @if ( $ticketManagement->works->count() )
                                @foreach ( $ticketManagement->works as $work )
                                    <div class="row margin-bottom-10">
                                        <div class="col-xs-9">
                                            {{ $work->name }}
                                        </div>
                                        <div class="col-xs-2">
                                            {{ $work->quantity }}
                                        </div>
                                    </div>
                                    <hr />
                                @endforeach
                            @else
                                <div class="small text-danger">Выполненных работ нет</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ( \Auth::user()->can( 'tickets.comments' ) )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <h4>Комментарии</h4>
                        @if ( $comments->count() )
                            <div id="ticket-comments">
                                @include( 'parts.comments', [ 'ticketManagement' => $ticketManagement, 'comments' => $comments ] )
                            </div>
                        @else
                            <div class="small text-danger" id="ticket-comments">Комментарии отсутствуют</div>
                        @endif
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
        @elseif ( $ticket && $ticket->canComment() )
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