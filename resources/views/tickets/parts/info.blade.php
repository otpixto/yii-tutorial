<div class="row">
    <div class="col-lg-6">
        <div class="status">
            <div class="status-name">
                <span class="text-muted small">Статус:</span>
                <span id="status">
                    {{ $ticketManagement->status_name ?? $ticket->status_name }}
                </span>
                @if ( $ticket->status_code == 'waiting' && $ticket->postponed_to )
                    до {{ $ticket->postponed_to->format( 'd.m.Y' ) }}
                @endif
                @if ( $ticketManagement && $ticketManagement->rate )
                    <span data-edit="rate" data-id="{{ $ticketManagement->id }}">
                        @include( 'tickets.parts.rate' )
                    </span>
                @endif
            </div>
            <div class="progress" id="progress">
                @if ( $progressData[ 'percent' ] )
                    <div class="{{ $progressData[ 'class' ] }}" role="progressbar" style="width: {{ $progressData[ 'percent' ] }}%"></div>
                @endif
            </div>
            <div id="status-title">
                {{ $progressData[ 'title' ] }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 hidden-print">
        @if ( $ticketManagement && $ticketManagement->status_code == 'closed_with_confirm' && ! $ticketManagement->rate )
            @include( 'tickets.parts.rate_form' )
        @else
            <div class="margin-top-15">
                @if ( count( $availableStatuses ) )
                    @if ( \Auth::user()->can( 'supervisor.all_statuses.edit' ) )
                        {!! Form::open( [ 'url' => route( 'tickets.status', $ticketManagement ? $ticketManagement->getTicketNumber() : $ticket->id ), 'class' => 'd-inline submit-loading form-horizontal' ] ) !!}
                        {!! Form::hidden( 'model_name', get_class( $ticketManagement ?? $ticket ) ) !!}
                        {!! Form::hidden( 'model_id', ( $ticketManagement ?? $ticket )->id ) !!}
                        <div class="input-group input-group-lg">
                            <select name="status_code" id="status_code" class="form-control select2">
                                <option value="">
                                    -- выберите из списка --
                                </option>
                                @foreach( $availableStatuses as $status_code => $availableStatus )
                                    <option value="{{ $status_code }}">
                                        {{ $availableStatus[ 'status_name' ] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                {!! Form::submit( 'Применить', [ 'class' => 'btn btn-success' ] ) !!}
                            </span>
                        </div>
                        {!! Form::close() !!}
                    @else
                        @foreach( $availableStatuses as $status_code => $availableStatus )
                            @if ( ( $ticketManagement ?? $ticket )->status_code == $status_code || ( \App\Models\Provider::getCurrent() && \App\Models\Provider::$current->need_act && $ticket->type->need_act && $status_code == 'completed_without_act' ) )
                                @php
                                    continue;
                                @endphp
                            @endif
                            {!! Form::open( [ 'url' => $availableStatus[ 'url' ], 'data-status' => $status_code, 'data-id' => $availableStatus[ 'model_id' ], 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "' . $availableStatus[ 'status_name' ] . '"?' ] ) !!}
                            {!! Form::hidden( 'model_name', $availableStatus[ 'model_name' ] ) !!}
                            {!! Form::hidden( 'model_id', $availableStatus[ 'model_id' ] ) !!}
                            {!! Form::hidden( 'status_code', $status_code ) !!}
                            {!! Form::submit( \App\Models\Ticket::$statuses_buttons[ $status_code ][ 'name' ] ?? $availableStatus[ 'status_name' ], [ 'class' => 'btn btn-lg margin-bottom-5 margin-right-5 ' . ( \App\Models\Ticket::$statuses_buttons[ $status_code ][ 'class' ] ?? 'btn-primary' ) ] ) !!}
                            {!! Form::close() !!}
                        @endforeach
                    @endif
                @endif
            </div>
        @endif

    </div>
</div>

@if ( $ticketManagement->ticket->status_code == 'waiting' && ! empty( $ticketManagement->ticket->postponed_comment ) )
    <div class="alert alert-warning">
        {{ $ticketManagement->ticket->postponed_comment }}
    </div>
@endif

<ul class="nav nav-tabs margin-top-15 margin-bottom-0">
    <li class="active">
        <a href="#main" class="tooltips" title="Основное">
            <i class="icon-info"></i>
        </a>
    </li>
    @if ( $ticket->phone )
        <li>
            <a href="#customer_tickets" class="tooltips" title="Заявки с этого телефона">
                <i class="icon-call-out"></i>
                <span class="badge {{ $customerTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                    {{ $customerTicketsCount }}
                </span>
            </a>
        </li>
    @endif
    <li>
        <a href="#address_tickets" class="tooltips" title="Заявки по этому адресу">
            <i class="icon-home"></i>
            <span class="badge {{ $addressTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                {{ $addressTicketsCount }}
            </span>
        </a>
    </li>
    <li>
        <a href="#neighbors_tickets" class="tooltips" title="Заявки соседей">
            <i class="icon-users"></i>
            <span class="badge {{ $neighborsTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                {{ $neighborsTicketsCount }}
            </span>
        </a>
    </li>
    <li>
        <a href="#works" class="tooltips" title="Отключения">
            <i class="icon-wrench"></i>
            <span class="badge {{ $worksCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                {{ $worksCount }}
            </span>
        </a>
    </li>
    @if ( $ticketManagement && \Auth::user()->can( 'tickets.services.show' ) )
        <li>
            <a href="#services" class="tooltips" title="Выполненные работы">
                <i class="icon-list"></i>
                <span class="badge {{ $servicesCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                    {{ $servicesCount }}
                </span>
            </a>
        </li>
    @endif
    <li>
        <a href="#other" class="tooltips" title="Остальное">
            <i class="icon-question"></i>
        </a>
    </li>
    <li>
        <a href="#location" class="tooltips" title="Геопозиция">
            <i class="icon-map"></i>
        </a>
    </li>
    @if ( \Auth::user()->can( 'tickets.history' ) )
        <li>
            <a href="#history" class="tooltips" title="История изменений">
                <i class="icon-notebook"></i>
            </a>
        </li>
    @endif
</ul>

<div class="tab-content">

    <div id="main" class="tab-pane fade in active">

        <div class="row">
            <div class="col-lg-6">

                {{--@if ( $ticketManagement && $ticketManagement->canRate() )
                    <div class="row hidden-print">
                        <div class="col-xs-12">
                            @include( 'parts.rate_form', [ 'ticketManagement' => $ticketManagement ] )
                        </div>
                    </div>
                @endif--}}

                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="type">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Классификатор:
                                </dt>
                                <dd>
                                    {{ $ticket->type->name ?? '' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="building">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Адрес проблемы:
                                </dt>
                                <dd>
                                    @if ( $ticket->building )
                                        {{ $ticket->getAddress() }}
                                        <span class="small text-muted">
                                            ({{ $ticket->getPlace() }})
                                        </span>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="text">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Текст заявки:
                                </dt>
                                <dd>
                                    {{ $ticket->text ?: '-' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-6">

                <div class="row">
                    <div class="col-xs-6">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="name">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    ФИО Заявителя:
                                </dt>
                                <dd>
                                    {{ $ticket->getName() ?: '-' }}
                                </dd>
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
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="phone">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Телефон(ы) Заявителя:
                                </dt>
                                <dd>
                                    {{ $ticket->getPhones() ?: '-' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="actual_building">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Адрес проживания:
                                </dt>
                                <dd>
                                    @if ( $ticket->actualBuilding )
                                        {{ $ticket->getActualAddress() }}
                                    @else
                                        -
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-toggle="#tags_edit, #tags_show">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Теги
                                </dt>
                                <dd>
                                    @if ( $ticket->canEdit() )
                                        <div id="tags_edit" class="hidden">
                                            {!! Form::text( 'tags', $ticket->tags->implode( 'text', ',' ), [ 'class' => 'form-control input-large', 'data-role' => 'tagsinput', 'autocomplete' => 'off', 'id' => 'tags' ] ) !!}
                                        </div>
                                    @endif
                                    <div id="tags_show" class="margin-top-10">
                                        @forelse ( $ticket->tags as $tag )
                                            <a href="{{ route( 'tickets.index', [ 'tags' => $tag->text ] ) }}" class="label label-info small margin-right-10">
                                                #{{ $tag->text }}
                                            </a>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="note">
                            <dl>
                                <dt>
                                    @if ( $ticket->canEdit() )
                                        <a href="javascript:;" class="hidden-print" data-edit="mark">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endif
                                    Дополнительные метки:
                                </dt>
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
                                    @if ( $ticket->type && $ticket->type->need_act )
                                        <span class="badge bg-purple-plum bold">
                                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                                    Требуется Акт выполненных работ
                                </span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @if ( $ticketManagement )

            <table class="table">
                <thead>
                <tr>
                    <th width="35%">
                        @if ( $ticketManagement->canSetManagement() )
                            <a href="javascript:;" class="hidden-print" data-edit="managements">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                        УО
                    </th>
                    <th width="35%">
                        @if ( $ticketManagement->canSetExecutor() )
                            <a href="javascript:;" class="hidden-print" data-edit="executor">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                        Исполнитель
                    </th>
                    <th width="30%">
                        @if ( $ticketManagement->canSetExecutor() )
                            <a href="javascript:;" class="hidden-print" data-edit="executor">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                        Запланировано
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if ( $ticket->canCall() )
                            <button type="button" class="btn btn-lg btn-warning pull-right margin-left-10 hidden-print" data-action="ticket-call" data-ticket="{{ $ticket->id }}" data-phones="{{ $ticketManagement->phone }}">
                                <i class="fa fa-phone"></i>
                            </button>
                        @endif
                        @if ( $ticketManagement->management->parent )
                            <div class="text-muted">
                                {{ $ticketManagement->management->parent->name }}
                            </div>
                        @endif
                        {{ $ticketManagement->management->name }}
                        @if ( $ticketManagement->management->building )
                            <div class="small">
                                {{ $ticketManagement->management->building->name }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if ( $ticketManagement->executor )
                            {{ $ticketManagement->executor->name }}
                        @else
                            <span class="text-danger">
                                Исполнитель не назначен
                            </span>
                        @endif
                    </td>
                    <td>
                        @if ( $ticketManagement->scheduled_begin && $ticketManagement->scheduled_end )
                            {{ $ticketManagement->scheduled_begin->format( 'd.m.Y H:i' ) }}
                            -
                            {{ $ticketManagement->scheduled_end->format( 'd.m.Y H:i' ) }}
                        @else
                            <span class="text-danger">
                                Не запланировано
                            </span>
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>

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
                                        @if ( $ticket->canCall() )
                                            <button type="button" class="btn btn-lg btn-warning pull-right margin-left-10 hidden-print" data-action="ticket-call" data-ticket="{{ $ticket->id }}" data-phones="{{ $_ticketManagement->management->getPhones() }}">
                                                <i class="fa fa-phone"></i>
                                            </button>
                                        @endif
                                        <dt>
                                            <a href="{{ route( 'tickets.show', $_ticketManagement->getTicketNumber() ) }}">
                                                @if ( $_ticketManagement->management->parent )
                                                    <div class="text-muted">
                                                        {{ $_ticketManagement->management->parent->name }}
                                                    </div>
                                                @endif
                                                {{ $_ticketManagement->management->name }}
                                            </a>
                                        </dt>
                                        <dd class="small">
                                            {{ $_ticketManagement->management->getPhones() }}
                                        </dd>
                                        @if ( $_ticketManagement->management->building )
                                            <dd class="small">
                                                {{ $_ticketManagement->management->building->name }}
                                            </dd>
                                        @endif
                                        @if ( ! $_ticketManagement->management->has_contract )
                                            <div class="margin-top-10">
                                                <span class="label label-danger">
                                                    Отсутствует договор
                                                </span>
                                            </div>
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

    </div>

    <div id="customer_tickets" class="tab-pane fade margin-top-15"></div>
    <div id="neighbors_tickets" class="tab-pane fade margin-top-15"></div>
    <div id="address_tickets" class="tab-pane fade margin-top-15"></div>
    <div id="works" class="tab-pane fade margin-top-15"></div>
    <div id="services" class="tab-pane fade margin-top-15"></div>
    @if ( \Auth::user()->can( 'tickets.history' ) )
        <div id="history" class="tab-pane fade margin-top-15"></div>
    @endif

    <div id="location" class="tab-pane fade margin-top-15">

        <div id="location-map" style="height: 500px;"></div>

    </div>

    <div id="other" class="tab-pane fade margin-top-15">

        @if ( $ticket->type )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <dl>
                            <dt>Сезонность устранения:</dt>
                            <dd>{{ $ticket->type->season ?? '-' }}</dd>
                        </dl>
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

        @if ( $ticket->transferred_at )
            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Заявка передана в УО:</dt>
                            <dd>{{ $ticket->transferred_at->format( 'd.m.Y H:i' ) }}</dd>
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
        @endif

        <hr />

        @if ( $ticket->deadline_acceptance && $ticket->deadline_execution )
            <div class="row">
                <div class="col-xs-6">
                    <div class="note note-{{ $ticket->overdueDeadlineAcceptance() ? 'danger' : 'success' }}">
                        <div class="row">
                            <div class="col-xs-6">
                                <dl>
                                    <dt>Принять до:</dt>
                                    <dd>
                                        {{ $ticket->deadline_acceptance->format( 'd.m.Y H:i' ) }}
                                    </dd>
                                </dl>
                            </div>
                            @if ( $ticket->accepted_at )
                                <div class="col-xs-6">
                                    <dl>
                                        <dt>Принято:</dt>
                                        <dd>
                                            {{ $ticket->accepted_at->format( 'd.m.Y H:i' ) }}
                                        </dd>
                                    </dl>
                                </div>
                            @endif
                        </div>
                        @if ( $ticket->overdueDeadlineAcceptance() )
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
                </div>
                <div class="col-xs-6">
                    <div class="note note-{{ $ticket->overdueDeadlineExecution() ? 'danger' : 'success' }}">
                        <div class="row">
                            <div class="col-xs-6">
                                <dl>
                                    <dt>Выполнить до:</dt>
                                    <dd>
                                        {{ $ticket->deadline_execution->format( 'd.m.Y H:i' ) }}
                                    </dd>
                                </dl>
                            </div>
                            @if ( $ticket->completed_at )
                                <div class="col-xs-6">
                                    <dl>
                                        <dt>Выполнено:</dt>
                                        <dd>
                                            {{ $ticket->completed_at->format( 'd.m.Y H:i' ) }}
                                        </dd>
                                    </dl>
                                </div>
                            @endif
                        </div>
                        @if ( $ticket->overdueDeadlineExecution() )
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
                </div>
            </div>
        @endif

        @if ( ! is_null( $ticket->duration_work ) )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note note-info">
                        <b>Продолжительность работы УО в часах: </b>
                        {{ $ticket->duration_work }}
                    </div>
                </div>
            </div>
        @endif

        @if ( $ticketManagement && $ticketManagement->rate )
            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        @if ( $ticketManagement->canRate() )
                            <a href="javascript:;" class="hidden-print" data-edit="rate" data-id="{{ $ticketManagement->id }}">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                        <b>Оценка работы УО: </b>
                        @include( 'tickets.parts.rate', [ 'ticketManagement' => $ticketManagement ] )
                        @if ( $ticketManagement->rate_comment )
                            <p>
                                {{ $ticketManagement->rate_comment }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-xs-6">
                @if ( ( \Auth::user()->can( 'admin.calls.all' ) || ( \Auth::user()->can( 'admin.calls.my' ) && \Auth::user()->id == $ticket->author_id ) ) && $ticket->cdr && $ticket->cdr->hasMp3() )
                    <div class="note">
                        <a href="{{ $ticket->cdr->getMp3() }}" target="_blank">
                            <i class="fa fa-chevron-circle-down text-success"></i>
                            Входящий вызов
                        </a>
                    </div>
                @endif
            </div>
            <div class="col-xs-6">
                @if ( $ticketCalls->count() )
                    @foreach ( $ticketCalls as $ticketCall )
                        @if ( $ticketCall->cdr && $ticketCall->cdr->hasMp3() )
                            <div class="note">
                                <a href="{{ $ticketCall->cdr->getMp3() }}" target="_blank">
                                    <i class="fa fa-chevron-circle-up text-danger"></i>
                                    Исходящий вызов
                                    <span class="text-muted small">
                                        {{ $ticketCall->created_at->format( 'd.m.Y H:i' ) }}
                                    </span>
                                </a>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

    </div>

</div>

@if ( \Auth::user()->can( 'tickets.comments' ) )
    <div class="row margin-top-15">
        <div class="col-xs-12">
            <div class="note">
                <div class="row">
                    <div class="col-md-6">
                        <h4>
                            Комментарии
                        </h4>
                    </div>
                    <div class="col-md-6 text-right">
                        @if ( $ticket && $ticket->canComment() )
                            <button type="button" class="btn btn-primary hidden-print" data-action="comment" data-model-name="{{ get_class( $ticket ) }}" data-model-id="{{ $ticket->id }}" data-origin-model-name="{{ get_class( $ticket ) }}" data-origin-model-id="{{ $ticket->id }}" data-file="1">
                                <i class="fa fa-commenting"></i>
                                Добавить комментарий
                            </button>
                        @endif
                    </div>
                </div>
                <div data-ticket-comments="{{ $ticket->id }}">
                    @if ( $comments->count() )
                        @include( 'parts.comments', [ 'origin' => $ticket, 'comments' => $comments ] )
                    @else
                        <div class="small text-danger">
                            Комментарии отсутствуют
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif